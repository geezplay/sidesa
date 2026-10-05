// SIADESA API Client - penghubung frontend ke backend Express + PostgreSQL
const BASE_URL = (import.meta.env.VITE_API_URL || 'http://localhost:5000').replace(/\/$/, '');

const TOKEN_KEY = 'siadesa_token';

export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token) {
    if (token) localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
}

async function request(path, { method = 'GET', body = null, isForm = false } = {}) {
    const headers = {};
    const token = getToken();
    if (token) headers['Authorization'] = `Bearer ${token}`;

    let payload = body;
    if (body && !isForm) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(BASE_URL + path, { method, headers, body: payload });
    } catch (networkError) {
        return { success: false, message: 'Tidak dapat terhubung ke server API. Periksa koneksi Anda.', network: true };
    }

    let data = {};
    try {
        data = await response.json();
    } catch (e) {
        data = {};
    }

    if (response.status === 401 && token) {
        // Token kedaluwarsa / tidak valid
        clearToken();
    }

    if (!response.ok) {
        return {
            success: false,
            status: response.status,
            message: data.message || 'Terjadi kesalahan pada server.',
            ...data
        };
    }

    return { success: true, ...data };
}

export const api = {
    baseUrl: BASE_URL,
    get: (path) => request(path, { method: 'GET' }),
    post: (path, body) => request(path, { method: 'POST', body }),
    put: (path, body) => request(path, { method: 'PUT', body }),
    patch: (path, body) => request(path, { method: 'PATCH', body }),
    del: (path) => request(path, { method: 'DELETE' }),
    postForm: (path, formData) => request(path, { method: 'POST', body: formData, isForm: true }),
    putForm: (path, formData) => request(path, { method: 'PUT', body: formData, isForm: true })
};

export default api;
