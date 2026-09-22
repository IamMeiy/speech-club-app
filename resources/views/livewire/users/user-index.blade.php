<div class="space-y-6 max-w-7xl mx-auto"
     x-data="{
         modalOpen: false,
         loading: false,
         errorMessage: null,
         activeTab: 'taken',
         memberData: null,
         async openRoles(userId) {
             if (this.loading) return;
             this.modalOpen = true;
             this.loading = true;
             this.errorMessage = null;
             this.memberData = null;
             this.activeTab = 'taken';
             try {
                 this.memberData = await $wire.getMemberRoles(userId);
             } catch (err) {
                 console.error('Error loading roles:', err);
                 this.errorMessage = 'Unable to load member roles. Please try again.';
             } finally {
                 this.loading = false;
             }
         },
         closeModal() {
             this.modalOpen = false;
             this.errorMessage = null;
         }
     }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                @if($club) {{ $club->name }} Members @else All Members @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage club enrollment, active roles, and contact info</p>
        </div>
        @can('users.create')
        <a href="{{ route('members.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Add Member
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 transition-colors">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search members by name or email…"
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="status" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
            <select wire:model.live="role" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Roles</option>
                @foreach($clubRoles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email Address</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Club Role</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                {{-- Skeleton Loading state during search, filter, and pagination --}}
                <tbody wire:loading.table-row-group wire:target="search, status, role, previousPage, nextPage, gotoPage" style="display: none;">
                    <x-skeleton.member-rows :rows="8" />
                </tbody>

                {{-- Real Data Body --}}
                <tbody wire:loading.remove wire:target="search, status, role, previousPage, nextPage, gotoPage" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('members.show', $user) }}" class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 text-white font-extrabold text-xs shadow-sm hover:scale-105 transition-transform" title="View {{ $user->name }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </a>
                                <div>
                                    <a href="{{ route('members.show', $user) }}" class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                        {{ $user->name }}
                                    </a>
                                    @if($user->phone)
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $user->roles->first()?->name ?? 'Member' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($user->status === 'inactive' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300') }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- View Roles Taken Action (Renderless, 0 table re-render) --}}
                                <button type="button"
                                        @click="openRoles({{ $user->id }})"
                                        class="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors group relative"
                                        title="View Roles Taken">
                                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </button>

                                <a href="{{ route('members.show', $user) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
                                   title="View Member Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @can('users.update')
                                <a href="{{ route('members.edit', $user) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
                                   title="Edit Member">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('users.delete')
                                <button
                                    x-data
                                    @click="$confirm({
                                        title: 'Delete Member',
                                        message: 'Are you sure you want to delete {{ addslashes($user->name) }}? This cannot be undone.',
                                        type: 'danger',
                                        confirmText: 'Delete Member',
                                        onConfirm: () => $wire.deleteUser({{ $user->id }})
                                    })"
                                    class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors"
                                    title="Delete Member">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">No members found</p>
                            <p class="text-slate-400 text-xs mt-1">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- Roles Taken Modal (Alpine-managed, zero table re-render via #[Renderless]) --}}
    {{-- ========================================================================= --}}
    <div x-show="modalOpen"
         x-cloak
         x-on:keydown.escape.window="closeModal()"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 transition-opacity">

        {{-- Backdrop Click Area (Instant client-side close) --}}
        <div class="fixed inset-0" @click="closeModal()" aria-hidden="true"></div>

        {{-- Modal Content Card --}}
        <div class="relative w-full max-w-3xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden z-10 flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200"
             @click.stop>

            {{-- Loading State Spinner --}}
            <div x-show="loading" class="p-16 text-center space-y-4">
                <div class="w-12 h-12 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Loading member roles…</p>
            </div>

            {{-- Error State Card --}}
            <div x-show="!loading && errorMessage" class="p-12 text-center space-y-4">
                <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Could not load member roles</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="errorMessage"></p>
                </div>
                <button type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">
                    Close
                </button>
            </div>

            {{-- Loaded Content --}}
            <template x-if="!loading && memberData && !errorMessage">
                <div class="flex flex-col flex-1 min-h-0">
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 text-white font-extrabold text-sm shadow-md shadow-indigo-500/20"
                                 x-text="memberData.user.initials">
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate" x-text="memberData.user.name"></h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60"
                                          x-text="memberData.user.role"></span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 flex items-center gap-2">
                                    <span x-text="memberData.user.email"></span>
                                    <template x-if="memberData.user.clubs">
                                        <span class="flex items-center gap-2">
                                            <span>•</span>
                                            <span class="font-medium text-slate-600 dark:text-slate-300" x-text="memberData.user.clubs"></span>
                                        </span>
                                    </template>
                                </p>
                            </div>
                        </div>

                        {{-- Close Button (Instant client-side close) --}}
                        <button type="button"
                                @click="closeModal()"
                                class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors flex-shrink-0"
                                title="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Summary Stats Bar --}}
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/20">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                            {{-- Taken Roles --}}
                            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-2xl border border-emerald-200/60 dark:border-emerald-900/40 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Taken</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-0.5 block" x-text="memberData.total_taken_count"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">roles completed</span>
                            </div>

                            {{-- Not Taken Roles --}}
                            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-2xl border border-amber-200/60 dark:border-amber-900/40 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Not Taken</span>
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                </div>
                                <span class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-0.5 block" x-text="memberData.total_not_taken"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">roles available</span>
                            </div>

                            {{-- Total Sessions --}}
                            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-2xl border border-indigo-200/60 dark:border-indigo-900/40 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Sessions</span>
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                </div>
                                <span class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mt-0.5 block" x-text="memberData.total_sessions"></span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">total times served</span>
                            </div>

                            {{-- Role Coverage --}}
                            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 shadow-xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Breadth</span>
                                    <span class="text-2xl font-black text-slate-900 dark:text-white mt-0.5 block"
                                          x-text="Math.round((memberData.total_taken_count / Math.max(1, memberData.total_taken_count + memberData.total_not_taken)) * 100) + '%'"></span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden mt-1">
                                    <div class="bg-gradient-to-r from-emerald-500 to-indigo-600 h-full rounded-full transition-all duration-500"
                                         :style="'width: ' + Math.round((memberData.total_taken_count / Math.max(1, memberData.total_taken_count + memberData.total_not_taken)) * 100) + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabs Navigation --}}
                    <div class="flex items-center gap-1 sm:gap-2 px-6 pt-3 border-b border-slate-100 dark:border-slate-800 overflow-x-auto bg-slate-50/20 dark:bg-slate-800/10">
                        {{-- Tab 1: Taken Roles --}}
                        <button type="button"
                                @click="activeTab = 'taken'"
                                :class="activeTab === 'taken' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                                class="pb-3 px-3 text-xs sm:text-sm border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Taken Roles</span>
                            <span class="px-2 py-0.5 text-[11px] rounded-full font-bold"
                                  :class="activeTab === 'taken' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                                  x-text="memberData.taken_roles.length"></span>
                        </button>

                        {{-- Tab 2: Not Taken Yet --}}
                        <button type="button"
                                @click="activeTab = 'not_taken'"
                                :class="activeTab === 'not_taken' ? 'border-amber-600 text-amber-600 dark:text-amber-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                                class="pb-3 px-3 text-xs sm:text-sm border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Not Taken Yet</span>
                            <span class="px-2 py-0.5 text-[11px] rounded-full font-bold"
                                  :class="activeTab === 'not_taken' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                                  x-text="memberData.not_taken_roles.length"></span>
                        </button>

                        {{-- Tab 3: Recently Taken --}}
                        <button type="button"
                                @click="activeTab = 'recently_taken'"
                                :class="activeTab === 'recently_taken' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                                class="pb-3 px-3 text-xs sm:text-sm border-b-2 transition-colors flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Recently Taken</span>
                            <span class="px-2 py-0.5 text-[11px] rounded-full font-bold"
                                  :class="activeTab === 'recently_taken' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                                  x-text="memberData.recently_taken.length"></span>
                        </button>
                    </div>

                    {{-- Tab Content (Scrollable) --}}
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">

                        {{-- Tab 1: Taken Roles --}}
                        <div x-show="activeTab === 'taken'" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="role in memberData.taken_roles" :key="role.name">
                                    <div class="flex items-start justify-between p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all hover:shadow-sm">
                                        <div class="min-w-0 pr-3">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider"
                                                      :class="{
                                                          'bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300': role.category === 'Facilitator',
                                                          'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300': role.category === 'Impromptu',
                                                          'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300': role.category === 'Evaluation',
                                                          'bg-primary-100 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300': role.category === 'Speaking',
                                                          'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !['Facilitator', 'Impromptu', 'Evaluation', 'Speaking'].includes(role.category)
                                                      }"
                                                      x-text="role.category"></span>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="role.name"></h4>
                                                    <template x-if="role.full_name">
                                                        <span class="text-xs font-normal text-slate-400 dark:text-slate-500" x-text="'(' + role.full_name + ')'"></span>
                                                    </template>
                                                </div>
                                            </div>

                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 space-y-0.5">
                                                <template x-if="role.last_date || role.last_meeting">
                                                    <p class="flex items-center gap-1.5 flex-wrap">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        <span>Last served:</span>
                                                        <template x-if="role.last_meeting_url">
                                                            <a :href="role.last_meeting_url" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline" x-text="'Meeting ' + role.last_meeting"></a>
                                                        </template>
                                                        <template x-if="!role.last_meeting_url && role.last_meeting">
                                                            <span class="font-medium text-slate-700 dark:text-slate-300" x-text="'Meeting ' + role.last_meeting"></span>
                                                        </template>
                                                        <template x-if="role.last_date">
                                                            <span class="text-slate-400" x-text="'(' + role.last_date + ')'"></span>
                                                        </template>
                                                    </p>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="flex-shrink-0 text-right">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-2xs">
                                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span x-text="role.count + (role.count === 1 ? ' time' : ' times')"></span>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="memberData.taken_roles.length === 0">
                                <div class="py-12 text-center">
                                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No roles taken yet</p>
                                    <p class="text-xs text-slate-400 mt-1">This member has not yet served in any meeting roles.</p>
                                </div>
                            </template>
                        </div>

                        {{-- Tab 2: Not Taken Yet --}}
                        <div x-show="activeTab === 'not_taken'" x-cloak class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="role in memberData.not_taken_roles" :key="role.name">
                                    <div class="flex items-center justify-between p-4 rounded-2xl bg-amber-50/20 dark:bg-amber-950/10 border border-dashed border-amber-300/80 dark:border-amber-800/60 hover:border-amber-400 dark:hover:border-amber-700 transition-all">
                                        <div class="min-w-0 pr-3">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider"
                                                      :class="{
                                                          'bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300': role.category === 'Facilitator',
                                                          'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300': role.category === 'Impromptu',
                                                          'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300': role.category === 'Evaluation',
                                                          'bg-primary-100 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300': role.category === 'Speaking',
                                                          'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !['Facilitator', 'Impromptu', 'Evaluation', 'Speaking'].includes(role.category)
                                                      }"
                                                      x-text="role.category"></span>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate" x-text="role.name"></h4>
                                                    <template x-if="role.full_name">
                                                        <span class="text-xs font-normal text-slate-400 dark:text-slate-500" x-text="'(' + role.full_name + ')'"></span>
                                                    </template>
                                                </div>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Never taken in club meetings yet</span>
                                            </p>
                                        </div>

                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80">
                                                Available
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="memberData.not_taken_roles.length === 0">
                                <div class="py-12 text-center">
                                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 rounded-2xl flex items-center justify-center mx-auto mb-3 text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <p class="text-base font-bold text-slate-900 dark:text-white">All Roles Mastered!</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Outstanding! This member has taken every single role in the club at least once.</p>
                                </div>
                            </template>
                        </div>

                        {{-- Tab 3: Recently Taken History --}}
                        <div x-show="activeTab === 'recently_taken'" x-cloak class="space-y-3">
                            <template x-for="(item, idx) in memberData.recently_taken" :key="idx">
                                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0"
                                             :class="{
                                                 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300': item.type === 'Facilitator',
                                                 'bg-primary-100 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300': item.type === 'Speaker',
                                                 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300': item.type === 'Evaluator',
                                                 'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300': item.type === 'Table Topics'
                                             }"
                                             x-text="item.role_name.substring(0, 3).toUpperCase()">
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="item.role_name"></h4>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold capitalize"
                                                      :class="item.status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'"
                                                      x-text="item.status"></span>
                                            </div>

                                            <p class="text-xs text-slate-600 dark:text-slate-300 truncate font-medium mt-0.5" x-text="item.detail"></p>

                                            <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5 truncate">
                                                <span x-text="'Meeting #' + item.meeting_number + ' • ' + item.meeting_date"></span>
                                                <template x-if="item.club_name">
                                                    <span x-text="' • ' + item.club_name"></span>
                                                </template>
                                            </p>
                                        </div>
                                    </div>

                                    <template x-if="item.meeting_url">
                                        <a :href="item.meeting_url"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/50 transition-colors flex-shrink-0"
                                           title="View Meeting">
                                            <span>Meeting</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="memberData.recently_taken.length === 0">
                                <div class="py-12 text-center">
                                    <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-indigo-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No recent role activity found</p>
                                    <p class="text-xs text-slate-400 mt-1">This member has no recent meeting role logs recorded.</p>
                                </div>
                            </template>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                        <a :href="memberData.user.profile_url"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            <span>Full Performance Profile & Speeches</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <button type="button"
                                @click="closeModal()"
                                class="px-5 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors">
                            Close
                        </button>
                    </div>

                </div>
            </template>

        </div>
    </div>

</div>
