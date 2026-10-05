@extends('layouts.app', ['role' => 'admin'])
@section('title', 'Kelola Akun Petugas')
@section('page_title', 'Manajemen Akun Petugas & Kepala Desa')
@section('content')

<script>
window.accountsManager = function() {
    return {
        users: [],
        currentUser: null,
        modalOpen: false,
        kadesModalOpen: false,
        isEditing: false,
        toastMsg: '',
        toastSuccess: true,
        errorMessage: '',
        form: {
            originalId: '',
            login_id: '',
            name: '',
            nik: '',
            phone: '',
            email: '',
            password: '',
            role: 'admin'
        },
        init() {
            this.refresh();
            if (window.siadesaStore) {
                this.currentUser = window.siadesaStore.getCurrentUser();
            }
        },
        refresh() {
            if (window.siadesaStore) {
                window.siadesaStore.fetchStaff().then(list => {
                    this.users = list || [];
                });
            }
        },
        get adminList() {
            return this.users.filter(u => u.role === 'admin');
        },
        get kadesUser() {
            return this.users.find(u => u.role === 'kades') || null;
        },
        isSelf(loginId) {
            return this.currentUser && (this.currentUser.id === loginId || this.currentUser.nik === loginId);
        },
        showToast(msg, ok = true) {
            this.toastMsg = msg;
            this.toastSuccess = ok;
            setTimeout(() => { this.toastMsg = ''; }, 3500);
        },
        openCreate() {
            this.isEditing = false;
            this.errorMessage = '';
            this.form = { originalId: '', login_id: '', name: '', nik: '', phone: '', email: '', password: '', role: 'admin' };
            this.modalOpen = true;
        },
        openEdit(u) {
            this.isEditing = true;
            this.errorMessage = '';
            this.form = {
                originalId: u.login_id,
                login_id: u.login_id,
                name: u.name || '',
                nik: u.nik || '',
                phone: u.phone || '',
                email: u.email || '',
                password: '',
                role: u.role
            };
            this.modalOpen = true;
        },
        openKades() {
            this.errorMessage = '';
            const k = this.kadesUser;
            this.form = {
                originalId: k ? k.login_id : '',
                login_id: k ? k.login_id : 'kades',
                name: k ? k.name : '',
                nik: k ? k.nik : '',
                phone: k ? k.phone : '',
                email: k ? k.email : '',
                password: '',
                role: 'kades'
            };
            this.isEditing = !!k;
            this.kadesModalOpen = true;
        },
        saveAdmin() {
            this.errorMessage = '';
            if (window.siadesaStore) {
                const op = this.isEditing
                    ? window.siadesaStore.updateStaffUser(this.form.originalId, this.form)
                    : window.siadesaStore.createStaffUser({ ...this.form, role: 'admin' });
                op.then(res => {
                    if (res.success) {
                        this.refresh();
                        this.modalOpen = false;
                        this.showToast(res.message);
                    } else {
                        this.errorMessage = res.message;
                    }
                });
            }
        },
        saveKades() {
            this.errorMessage = '';
            if (!this.form.name.trim() || !this.form.login_id.trim()) {
                this.errorMessage = 'Username dan Nama Kepala Desa wajib diisi!';
                return;
            }
            if (this.kadesUser && !this.form.password) {
                // update tanpa ganti password diperbolehkan
            } else if (!this.kadesUser && !this.form.password) {
                this.errorMessage = 'Kata sandi wajib diisi untuk akun Kepala Desa baru!';
                return;
            }
            if (window.siadesaStore) {
                const op = this.kadesUser
                    ? window.siadesaStore.updateStaffUser(this.form.originalId, this.form)
                    : window.siadesaStore.createStaffUser({ ...this.form, role: 'kades' });
                op.then(res => {
                    if (res.success) {
                        this.refresh();
                        this.kadesModalOpen = false;
                        this.showToast(res.message);
                    } else {
                        this.errorMessage = res.message;
                    }
                });
            }
        },
        removeUser(u) {
            if (!confirm('Hapus akun ' + u.name + ' (' + u.label + ')?')) return;
            if (window.siadesaStore) {
                window.siadesaStore.deleteStaffUser(u.login_id).then(res => {
                    if (res.success) {
                        this.refresh();
                        this.showToast(res.message);
                    } else {
                        this.showToast(res.message, false);
                    }
                });
            }
        }
    };
};
</script>

<div class="space-y-6" x-data="accountsManager()">

    <!-- Toast -->
    <template x-if="toastMsg">
        <div class="fixed top-20 right-6 z-50 p-4 rounded-2xl shadow-xl border flex items-center gap-3 transition-all"
             :class="toastSuccess ? 'bg-emerald-800 text-white border-emerald-600' : 'bg-rose-800 text-white border-rose-600'">
            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span class="text-xs sm:text-sm font-bold" x-text="toastMsg"></span>
        </div>
    </template>

    <!-- Info Header -->
    <div class="p-5 rounded-3xl bg-emerald-50 border border-emerald-200 flex gap-4 text-emerald-950 shadow-xs">
        <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
        </div>
        <div>
            <h3 class="font-extrabold text-sm sm:text-base">Manajemen Akun Petugas Desa</h3>
            <p class="text-xs text-emerald-800/90 mt-1 leading-relaxed">
                Admin Desa dapat menambahkan lebih dari satu akun admin/operator pelayanan. Akun <strong>Kepala Desa hanya satu</strong> dan dapat diperbarui.
            </p>
        </div>
    </div>

    <!-- Kartu Kepala Desa (Tunggal) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Akun Kepala Desa (Tunggal)
                </h3>
                <p class="text-xs text-slate-500">Bertugas menyetujui & mengesahkan seluruh permohonan surat.</p>
            </div>
            <button @click="openKades()" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span x-text="kadesUser ? 'Perbarui Akun Kepala Desa' : 'Tetapkan Akun Kepala Desa'"></span>
            </button>
        </div>

        <template x-if="kadesUser">
            <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 flex items-center gap-4">
                <div class="w-11 h-11 rounded-full bg-purple-700 text-white font-bold flex items-center justify-center text-sm" x-text="kadesUser.avatarText"></div>
                <div class="min-w-0">
                    <p class="font-bold text-slate-900 text-sm" x-text="kadesUser.name"></p>
                    <p class="text-xs text-slate-500 font-mono" x-text="'Username: ' + kadesUser.login_id"></p>
                    <p class="text-[11px] text-slate-400" x-text="'NIP: ' + (kadesUser.nik || '-')"></p>
                </div>
            </div>
        </template>
        <template x-if="!kadesUser">
            <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center">
                <p class="text-xs font-semibold text-amber-800">Belum ada akun Kepala Desa yang ditetapkan. Silakan tetapkan satu akun.</p>
            </div>
        </template>
    </div>

    <!-- Daftar Akun Admin (Banyak) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Akun Admin / Operator Pelayanan (<span x-text="adminList.length"></span>)
                </h3>
                <p class="text-xs text-slate-500">Dapat menambahkan admin kedua dan seterusnya untuk membantu pengelolaan layanan.</p>
            </div>
            <button @click="openCreate()" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Admin Baru</span>
            </button>
        </div>

        <div class="space-y-2">
            <template x-for="u in adminList" :key="u.login_id">
                <div class="p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-300 transition-all flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-emerald-700 text-white font-bold flex items-center justify-center text-xs flex-shrink-0" x-text="u.avatarText"></div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate" x-text="u.name"></p>
                            <p class="text-[11px] text-slate-500 font-mono truncate" x-text="'Username: ' + u.login_id + (u.phone ? ' • ' + u.phone : '')"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <span x-show="isSelf(u.login_id)" class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-300">Akun Anda</span>
                        <button @click="openEdit(u)" class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded-lg text-[11px] font-bold transition-colors">Edit</button>
                        <button @click="removeUser(u)" :disabled="isSelf(u.login_id)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 disabled:opacity-40 disabled:cursor-not-allowed text-rose-700 rounded-lg text-[11px] font-bold transition-colors">Hapus</button>
                    </div>
                </div>
            </template>
            <template x-if="adminList.length === 0">
                <p class="text-xs text-slate-400 text-center py-4">Belum ada akun admin.</p>
            </template>
        </div>
    </div>

    <!-- Modal Tambah/Edit Admin -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="modalOpen = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
            <h3 class="font-bold text-slate-900 text-base" x-text="isEditing ? 'Edit Akun Admin' : 'Tambah Akun Admin Baru'"></h3>
            <template x-if="errorMessage">
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>
            </template>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-700">Username Login *</label>
                    <input x-model="form.login_id" :disabled="isEditing" placeholder="contoh: admin2" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono disabled:opacity-60">
                </div>
                <div>
                    <label class="font-bold text-slate-700">Nama Lengkap Petugas *</label>
                    <input x-model="form.name" placeholder="Nama lengkap admin" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-slate-700">NIP / ID</label>
                        <input x-model="form.nik" placeholder="NIP" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">No. HP</label>
                        <input x-model="form.phone" placeholder="0812..." class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-700">Email (Opsional)</label>
                    <input x-model="form.email" placeholder="admin2@desa.go.id" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                </div>
                <div>
                    <label class="font-bold text-slate-700" x-text="isEditing ? 'Kata Sandi Baru (Kosongkan bila tidak diubah)' : 'Kata Sandi *'"></label>
                    <input type="password" x-model="form.password" :placeholder="isEditing ? '••••••••' : 'Min. 6 karakter'" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button @click="modalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
                <button @click="saveAdmin()" class="px-5 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800" x-text="isEditing ? 'Simpan Perubahan' : 'Buat Akun'"></button>
            </div>
        </div>
    </div>

    <!-- Modal Kepala Desa -->
    <div x-show="kadesModalOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="kadesModalOpen = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
            <h3 class="font-bold text-slate-900 text-base" x-text="kadesUser ? 'Perbarui Akun Kepala Desa' : 'Tetapkan Akun Kepala Desa'"></h3>
            <template x-if="errorMessage">
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>
            </template>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-700">Username Login *</label>
                    <input x-model="form.login_id" :disabled="!!kadesUser" placeholder="kades" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono disabled:opacity-60">
                </div>
                <div>
                    <label class="font-bold text-slate-700">Nama Kepala Desa / Lurah *</label>
                    <input x-model="form.name" placeholder="Drs. H. Mulyono" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold text-slate-700">NIP</label>
                        <input x-model="form.nik" placeholder="19680315..." class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">No. HP</label>
                        <input x-model="form.phone" placeholder="0812..." class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-700" x-text="kadesUser ? 'Kata Sandi Baru (Kosongkan bila tidak diubah)' : 'Kata Sandi *'"></label>
                    <input type="password" x-model="form.password" :placeholder="kadesUser ? '••••••••' : 'Min. 6 karakter'" class="mt-1 w-full px-3 py-2 bg-slate-50 border rounded-xl">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button @click="kadesModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
                <button @click="saveKades()" class="px-5 py-2 bg-purple-700 text-white rounded-xl text-xs font-bold hover:bg-purple-800" x-text="kadesUser ? 'Simpan Perubahan' : 'Tetapkan Akun'"></button>
            </div>
        </div>
    </div>

</div>
@endsection
