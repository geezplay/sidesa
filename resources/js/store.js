// SIADESA Store - Terhubung ke Backend Express + PostgreSQL (via API)
import Alpine from 'alpinejs';
import api, { getToken, setToken, clearToken } from './api';

const ROLE_LABEL = {
    warga: 'Masyarakat / Pemohon',
    admin: 'Admin Desa & Operator Pelayanan',
    kades: 'Kepala Desa'
};

const ROLE_REDIRECT = {
    warga: '/warga/dashboard',
    admin: '/admin/dashboard',
    kades: '/kades/dashboard'
};

function initials(name) {
    return (name || 'W').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}

function fmtDate(value) {
    if (!value) return '-';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const pad = (n) => String(n).padStart(2, '0');
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${pad(d.getDate())} ${bulan[d.getMonth()]} ${d.getFullYear()} - ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function fmtDateShort(value) {
    if (!value) return '-';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const STATUS_DISPLAY = (s) => (s || '').replace(/_/g, ' ');

class SiadesaStore {
    constructor() {
        this.cache = {
            services: [],
            residents: [],
            applications: [],
            approvals: [],
            staff: [],
            applicationDetails: {},
            site: null
        };
        this.ready = false;
    }

    // ================= BOOTSTRAP =================
    async bootstrap() {
        // Muat pengaturan website & layanan publik (bisa tanpa login)
        await Promise.all([this.loadSiteSettings(), this.fetchServices()]);

        // Muat sesi pengguna bila token tersedia
        if (getToken()) {
            const res = await api.get('/api/auth/me');
            if (res.success && res.user) {
                this._setSession(res.user);
            } else {
                clearToken();
                localStorage.removeItem('siadesa_auth_session');
            }
        }
        this.ready = true;
        return true;
    }

    // ================= SESSION =================
    _mapUser(u) {
        const role = (u.role || 'WARGA').toLowerCase();
        const resident = u.resident || {};
        return {
            id: u.nik || u.id,
            login_id: u.nik,
            backendId: u.id,
            role,
            name: u.name,
            nik: u.nik,
            email: u.email || '',
            kk: resident.family_card_no || '',
            birth_place: resident.birth_place || '',
            birth_date: resident.birth_date ? String(resident.birth_date).substring(0, 10) : '',
            gender: resident.gender || '',
            label: ROLE_LABEL[role] || 'Pengguna',
            avatarText: initials(u.name),
            phone: resident.phone_number || u.phone || '',
            address: resident.address || '',
            rt: resident.rt || '',
            rw: resident.rw || '',
            religion: resident.religion || '',
            marital: resident.marital_status || '',
            job: resident.occupation || '',
            is_profile_complete: !!(u.is_profile_complete || false),
            redirect: ROLE_REDIRECT[role] || '/warga/dashboard'
        };
    }

    _setSession(backendUser) {
        const session = this._mapUser(backendUser);
        localStorage.setItem('siadesa_auth_session', JSON.stringify(session));
        return session;
    }

    getCurrentUser() {
        const str = localStorage.getItem('siadesa_auth_session');
        return str ? JSON.parse(str) : null;
    }

    getCurrentRole() {
        const u = this.getCurrentUser();
        return u ? u.role : null;
    }

    // ================= AUTH =================
    async authenticate(loginId, password) {
        const res = await api.post('/api/auth/login', { loginId, password });
        if (!res.success) {
            return { success: false, message: res.message || 'ID atau Kata Sandi salah!' };
        }
        setToken(res.token);
        const session = this._setSession(res.user);
        await Promise.all([this.fetchServices(), this.loadSiteSettings()]);
        return { success: true, user: session, redirect: session.redirect };
    }

    async registerUser(userData) {
        const res = await api.post('/api/auth/register', {
            nik: userData.nik,
            name: userData.name,
            phone: userData.phone,
            password: userData.password
        });
        if (!res.success) {
            return { success: false, message: res.message || 'Pendaftaran gagal.' };
        }
        setToken(res.token);
        const session = this._setSession(res.user);
        await this.fetchServices();
        return { success: true, user: session, redirect: '/warga/dashboard' };
    }

    async updateProfile(nik, profileData) {
        const payload = {
            family_card_no: String(profileData.kk || '').replace(/[^0-9]/g, ''),
            full_name: (profileData.name || '').trim(),
            birth_place: (profileData.birth_place || '').trim(),
            birth_date: profileData.birth_date || '',
            gender: profileData.gender || 'Laki-Laki',
            address: (profileData.address || '').trim(),
            rt: String(profileData.rt || '001').replace(/[^0-9]/g, '').padStart(3, '0').slice(-3),
            rw: String(profileData.rw || '001').replace(/[^0-9]/g, '').padStart(3, '0').slice(-3),
            religion: profileData.religion || 'Islam',
            marital_status: profileData.marital || 'Kawin',
            occupation: profileData.job || 'Wiraswasta',
            phone_number: (profileData.phone || '').trim()
        };
        const res = await api.put('/api/auth/profile', payload);
        if (!res.success) {
            return { success: false, message: res.message || 'Gagal menyimpan data profil.' };
        }
        // Segarkan sesi
        const me = await api.get('/api/auth/me');
        if (me.success && me.user) this._setSession(me.user);
        return { success: true, message: res.message || 'Data profil berhasil disimpan!', redirect: '/warga/dashboard' };
    }

    logout() {
        clearToken();
        localStorage.removeItem('siadesa_auth_session');
        window.location.href = '/login';
    }

    // ================= SITE SETTINGS =================
    async loadSiteSettings() {
        const res = await api.get('/api/public/info');
        if (res.success) {
            const v = res.village || {};
            const s = res.settings || {};
            const mapped = {
                app_name: s.app_name || 'SIADESA',
                village_name: v.name || 'Desa Sukamaju',
                village_subname: `${v.district || 'Kec. Jonggol'}, ${v.regency || 'Kab. Bogor'}`,
                logo_url: s.logo_url || '/images/logo-desa.svg',
                head_name: v.official_head || '',
                head_nip: v.official_nip || '',
                phone: v.phone || '',
                email: v.email || '',
                address: v.address || ''
            };
            this.cache.site = mapped;
            localStorage.setItem('siadesa_site_settings', JSON.stringify(mapped));
            return mapped;
        }
        return this.getSiteSettings();
    }

    getSiteSettings() {
        const str = localStorage.getItem('siadesa_site_settings');
        if (str) {
            try { return JSON.parse(str); } catch (e) { /* ignore */ }
        }
        return {
            app_name: 'SIADESA',
            village_name: 'Desa Sukamaju',
            village_subname: 'Kec. Jonggol, Kab. Bogor',
            logo_url: '/images/logo-desa.svg',
            head_name: '', head_nip: '', phone: '', email: '', address: ''
        };
    }

    async saveSiteSettings(settings) {
        const payload = {
            village: {
                address: settings.address,
                phone: settings.phone,
                email: settings.email,
                official_head: settings.head_name,
                official_nip: settings.head_nip
            },
            settings: [
                { key: 'app_name', value: settings.app_name },
                { key: 'logo_url', value: settings.logo_url }
            ]
        };
        const res = await api.put('/api/admin/settings', payload);
        if (res.success) {
            const mapped = { ...this.getSiteSettings(), ...settings };
            localStorage.setItem('siadesa_site_settings', JSON.stringify(mapped));
            this.cache.site = mapped;
            return { success: true };
        }
        return { success: false, message: res.message };
    }

    // ================= SERVICES =================
    _mapService(s) {
        return {
            id: s.id,
            code: s.code,
            name: s.name,
            category: s.category || 'Surat Keterangan',
            description: s.description || '',
            estimation: (s.estimation_days || 1) + ' Hari Kerja',
            estimation_days: s.estimation_days || 1,
            requires_approval: s.requires_approval !== false,
            approver: 'Kepala Desa',
            is_active: s.is_active !== false,
            requirements: Array.isArray(s.requirements)
                ? s.requirements.map(r => (typeof r === 'string' ? r : r.name))
                : []
        };
    }

    async fetchServices() {
        // Coba endpoint admin bila sudah login, jika gagal pakai endpoint publik
        let res = { success: false };
        const role = this.getCurrentRole();
        if (role === 'admin') {
            res = await api.get('/api/admin/services');
            if (res.success) {
                const list = res.services || [];
                this.cache.services = list.map(s => this._mapService(s));
                return this.cache.services;
            }
        }
        res = await api.get('/api/public/services');
        if (res.success) {
            this.cache.services = (res.services || []).map(s => this._mapService(s));
        }
        return this.cache.services;
    }

    getServices() {
        return this.cache.services;
    }

    async createService(data) {
        const payload = {
            code: data.code,
            name: data.name,
            category: data.category,
            description: data.description,
            estimation_days: data.estimation_days || 1,
            requirements: data.requirements || []
        };
        const res = await api.post('/api/admin/services', payload);
        if (res.success) await this.fetchServices();
        return res;
    }

    async updateService(id, data) {
        const payload = {
            code: data.code,
            name: data.name,
            category: data.category,
            description: data.description,
            estimation_days: data.estimation_days || 1,
            is_active: data.is_active !== false,
            requirements: data.requirements || []
        };
        const res = await api.put(`/api/admin/services/${id}`, payload);
        if (res.success) await this.fetchServices();
        return res;
    }

    // ================= RESIDENTS =================
    _mapResident(r) {
        return {
            nik: r.nik,
            kk: r.family_card_no || '',
            name: r.full_name || r.name || '',
            birth_place: r.birth_place || '',
            birth_date: r.birth_date ? String(r.birth_date).substring(0, 10) : '',
            gender: r.gender || 'Laki-Laki',
            address: r.address || '',
            rt: r.rt || '',
            rw: r.rw || '',
            religion: r.religion || 'Islam',
            marital: r.marital_status || 'Kawin',
            job: r.occupation || 'Wiraswasta',
            phone: r.phone_number || '',
            has_account: !!r.user
        };
    }

    async fetchResidents(search = '') {
        const q = search ? `?search=${encodeURIComponent(search)}` : '';
        const res = await api.get('/api/admin/residents' + q);
        if (res.success) {
            this.cache.residents = (res.residents || []).map(r => this._mapResident(r));
        }
        return this.cache.residents;
    }

    getResidents() {
        return this.cache.residents;
    }

    isResidentRegistered(nik) {
        const r = this.cache.residents.find(x => x.nik === nik);
        return r ? !!r.has_account : false;
    }

    async addResident(data) {
        const res = await api.post('/api/admin/residents', data);
        if (res.success) await this.fetchResidents();
        return res;
    }

    async updateResident(nik, data) {
        const res = await api.put(`/api/admin/residents/${nik}`, data);
        if (res.success) await this.fetchResidents();
        return res;
    }

    async importResidents(list) {
        const res = await api.post('/api/admin/residents/import', { residents: list });
        if (res.success) await this.fetchResidents();
        return {
            success: res.success,
            importedCount: res.imported || 0,
            updatedCount: res.updated || 0,
            message: res.message
        };
    }

    // ================= APPLICATIONS =================
    _mapApplication(a) {
        const resident = a.resident || {};
        const service = a.service || {};
        const histories = a.histories || [];
        const timeline = histories.map((h, idx) => {
            const isLast = idx === histories.length - 1;
            const st = h.status || a.status;
            let marker = 'done';
            if (isLast && ['DIAJUKAN', 'MENUNGGU_PERSETUJUAN', 'DIPROSES', 'PERLU_PERBAIKAN'].includes(st)) marker = 'active';
            if (st === 'PERLU_PERBAIKAN') marker = 'warning';
            if (st === 'DITOLAK') marker = 'danger';
            return {
                title: STATUS_DISPLAY(st),
                time: fmtDate(h.created_at),
                status: marker,
                desc: h.notes || '',
                actor: h.actor_name || ''
            };
        });

        return {
            id: a.id,
            tracking_number: a.tracking_number,
            service_id: a.service_id,
            service_name: service.name || a.service_name || '-',
            service_code: service.code || a.service_code || 'SRT',
            applicant_nik: resident.nik || (a.user ? a.user.nik : '') || '',
            applicant_name: resident.full_name || (a.user ? a.user.name : '') || '',
            applicant_phone: resident.phone_number || '',
            applicant_address: resident.address || '',
            purpose: a.purpose || '',
            business_name: a.business_name || '',
            business_type: a.business_type || '',
            business_address: a.business_address || '',
            status: STATUS_DISPLAY(a.status),
            raw_status: a.status,
            created_at: fmtDateShort(a.created_at),
            updated_at: fmtDateShort(a.updated_at),
            letter_number: a.letter_number || '',
            qr_uuid: a.qr_uuid || '',
            verifier_notes: a.verifier_notes || '',
            approval_notes: a.approval_notes || '',
            documents: a.documents || [],
            timeline
        };
    }

    async fetchMyApplications() {
        const res = await api.get('/api/applications/my');
        if (res.success) {
            this.cache.applications = (res.applications || []).map(a => this._mapApplication(a));
        }
        return this.cache.applications;
    }

    async fetchStaffApplications() {
        const res = await api.get('/api/admin/verifications');
        if (res.success) {
            this.cache.applications = (res.applications || []).map(a => this._mapApplication(a));
        }
        return this.cache.applications;
    }

    async fetchApprovals() {
        const res = await api.get('/api/kades/approvals');
        if (res.success) {
            this.cache.applications = (res.approvals || []).map(a => this._mapApplication(a));
        }
        return this.cache.applications;
    }

    async fetchAllApplications() {
        const res = await api.get('/api/admin/applications');
        if (res.success) {
            this.cache.applications = (res.applications || []).map(a => this._mapApplication(a));
        }
        return this.cache.applications;
    }

    getApplications() {
        return this.cache.applications;
    }

    async fetchApplicationById(id) {
        const res = await api.get(`/api/applications/${id}`);
        if (res.success && res.application) {
            const mapped = this._mapApplication(res.application);
            this.cache.applicationDetails[id] = mapped;
            return mapped;
        }
        return null;
    }

    getApplicationById(id) {
        return this.cache.applicationDetails[id] || this.cache.applications.find(a => a.id === id) || null;
    }

    async trackApplication(code) {
        const res = await api.get(`/api/public/tracking/${encodeURIComponent(code)}`);
        if (res.success && res.application) {
            return this._mapApplication(res.application);
        }
        return null;
    }

    getApplicationByTracking(code) {
        return this.cache.applications.find(a => (a.tracking_number || '').toUpperCase() === (code || '').toUpperCase()) || null;
    }

    async verifyDocument(code) {
        const res = await api.get(`/api/public/verify-doc/${encodeURIComponent(code)}`);
        return res;
    }

    async addApplication(data) {
        const form = new FormData();
        form.append('service_id', data.service_id);
        form.append('purpose', data.purpose || '');
        if (data.business_name) form.append('business_name', data.business_name);
        if (data.business_type) form.append('business_type', data.business_type);
        if (data.business_address) form.append('business_address', data.business_address);
        if (Array.isArray(data.files)) {
            data.files.forEach(f => { if (f && f.file) form.append('documents', f.file); });
        }
        const res = await api.postForm('/api/applications', form);
        if (res.success && res.application) {
            return this._mapApplication(res.application);
        }
        return res;
    }

    async updateApplicationStatus(id, newStatus, notes = '') {
        const role = this.getCurrentRole();
        if (role === 'admin') {
            const decisionMap = {
                'MENUNGGU PERSETUJUAN': 'APPROVE',
                'MENUNGGU_PERSETUJUAN': 'APPROVE',
                'PERLU PERBAIKAN': 'REVISE',
                'PERLU_PERBAIKAN': 'REVISE',
                'DITOLAK': 'REJECT'
            };
            const decision = decisionMap[newStatus] || 'APPROVE';
            const res = await api.patch(`/api/admin/verifications/${id}`, { decision, notes });
            if (res.success && res.application) return this._mapApplication(res.application);
            return res;
        }
        if (role === 'kades') {
            const action = (newStatus === 'DITOLAK') ? 'REJECT' : 'APPROVE';
            const res = await api.patch(`/api/kades/approvals/${id}`, { action, notes });
            if (res.success && res.application) return this._mapApplication(res.application);
            return res;
        }
        // Warga: ajukan ulang perbaikan
        const res = await api.put(`/api/applications/${id}/resubmit`, {});
        if (res.success && res.application) return this._mapApplication(res.application);
        return res;
    }

    // ================= STAFF (Akun Petugas) =================
    _mapStaff(u) {
        const role = (u.role || 'ADMIN').toLowerCase();
        return {
            login_id: u.nik,
            backendId: u.id,
            name: u.name,
            nik: u.nik,
            email: u.email || '',
            phone: u.phone || '',
            role,
            label: ROLE_LABEL[role] || 'Admin Desa',
            avatarText: initials(u.name)
        };
    }

    async fetchStaff() {
        const res = await api.get('/api/admin/staff');
        if (res.success) {
            this.cache.staff = (res.staff || []).map(u => this._mapStaff(u));
        }
        return this.cache.staff;
    }

    getStaffUsers() {
        return this.cache.staff;
    }

    hasKadesAccount() {
        return this.cache.staff.some(u => u.role === 'kades');
    }

    async createStaffUser(data) {
        const res = await api.post('/api/admin/staff', {
            username: data.login_id,
            name: data.name,
            nik: data.nik,
            phone: data.phone,
            email: data.email,
            password: data.password,
            role: data.role
        });
        if (res.success) await this.fetchStaff();
        return res;
    }

    async updateStaffUser(loginId, data) {
        const target = this.cache.staff.find(u => u.login_id === loginId);
        if (!target) return { success: false, message: 'Akun tidak ditemukan.' };
        const res = await api.put(`/api/admin/staff/${target.backendId}`, {
            name: data.name,
            nik: data.nik,
            phone: data.phone,
            email: data.email,
            password: data.password || undefined
        });
        if (res.success) await this.fetchStaff();
        return res;
    }

    async deleteStaffUser(loginId) {
        const target = this.cache.staff.find(u => u.login_id === loginId);
        if (!target) return { success: false, message: 'Akun tidak ditemukan.' };
        const res = await api.del(`/api/admin/staff/${target.backendId}`);
        if (res.success) await this.fetchStaff();
        return res;
    }

    // ================= LEGACY / NO-OP (kompatibilitas view) =================
    setCurrentRole() { /* mode demo dihapus */ }
    resetToDefault() { /* tidak digunakan */ }
}

export const siadesaStore = new SiadesaStore();

if (typeof window !== 'undefined') {
    window.siadesaStore = siadesaStore;
    window.Alpine = Alpine;
}
