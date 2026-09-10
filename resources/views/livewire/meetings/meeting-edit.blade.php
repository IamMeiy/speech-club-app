<div class="max-w-4xl mx-auto space-y-6" x-data="{
    speakers: $wire.entangle('speakers'),
    ttmSpeakers: $wire.entangle('ttmSpeakers'),
    evaluations: $wire.entangle('evaluations'),
    members: $wire.entangle('membersList'),
    addSpeaker() {
        this.speakers.push({ user_id: '', topic: '', speech_type: '', project: '', duration: '' });
    },
    removeSpeaker(index) {
        if (this.speakers.length > 1) {
            this.speakers.splice(index, 1);
        }
    },
    addTtmSpeaker() {
        this.ttmSpeakers.push({ user_id: '', topic: '', duration: '' });
    },
    removeTtmSpeaker(index) {
        if (this.ttmSpeakers.length > 1) {
            this.ttmSpeakers.splice(index, 1);
        }
    },
    addEvaluation() {
        this.evaluations.push({ speaker_index: 0, evaluator_user_id: '' });
    },
    removeEvaluation(index) {
        if (this.evaluations.length > 1) {
            this.evaluations.splice(index, 1);
        }
    }
}">

    <div class="flex items-center gap-4">
        <a href="{{ route('meetings.show', $meeting) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Edit Meeting #{{ $meeting->meeting_number }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $meeting->club->name }}</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        @if($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-xs sm:text-sm text-rose-600 dark:text-rose-300 space-y-1 font-medium">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        {{-- Meeting Information --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Information</h2>
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meeting Date <span class="text-rose-500">*</span></label>
                        <input wire:model="meeting_date" type="date" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 @error('meeting_date') border-rose-300 @enderror">
                        @error('meeting_date') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meeting Number</label>
                        <input wire:model="meeting_number" type="number" min="1" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Theme</label>
                    <input wire:model="theme" type="text" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Venue</label>
                        <input wire:model="venue" type="text" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                        <select wire:model="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Notes</label>
                    <textarea wire:model="notes" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>
            </div>
        </div>

        {{-- Fixed Meeting Roles --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Roles</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($roleTypes as $roleType)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ $roleType->name }}</label>
                    <select wire:model="roleAssignments.{{ $roleType->id }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">— Select Member —</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Prepared Speakers (100% Client-Side Alpine.js) --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Prepared Speakers</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(speaker, index) in speakers" :key="index">
                    <div class="flex gap-3 items-start p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-950/80 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                            <span x-text="index + 1"></span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speaker</label>
                                <select x-model="speaker.user_id"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">— Select Member —</option>
                                    <template x-for="m in members" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Topic</label>
                                <input x-model="speaker.topic" type="text" placeholder="Speech topic"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speech Type</label>
                                <input x-model="speaker.speech_type" type="text" placeholder="e.g. Prepared / Ice Breaker"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Duration</label>
                                <input x-model="speaker.duration" type="text" placeholder="e.g. 5-7 min"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <button x-show="speakers.length > 1" type="button" @click="removeSpeaker(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0 mt-0.5" title="Remove speaker">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addSpeaker()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 hover:bg-primary-100 dark:hover:bg-primary-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Speaker
            </button>
        </div>

        {{-- TTM Speakers (100% Client-Side Alpine.js) --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">TTM Speakers</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(ttm, index) in ttmSpeakers" :key="index">
                    <div class="flex gap-3 items-start p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                            <span x-text="index + 1"></span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">TTM Speaker</label>
                                <select x-model="ttm.user_id"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">— Select Member —</option>
                                    <template x-for="m in members" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Topic</label>
                                <input x-model="ttm.topic" type="text" placeholder="TTM topic"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <button x-show="ttmSpeakers.length > 1" type="button" @click="removeTtmSpeaker(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0 mt-0.5" title="Remove TTM speaker">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addTtmSpeaker()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add TTM Speaker
            </button>
        </div>

        {{-- Evaluators (100% Client-Side Alpine.js) --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Evaluators</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(evalItem, index) in evaluations" :key="index">
                    <div class="flex gap-3 items-center p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speaker Being Evaluated</label>
                                <select x-model="evalItem.speaker_index"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                                    <template x-for="(sp, si) in speakers" :key="si">
                                        <option :value="si" x-text="'Speaker ' + (si + 1)"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Evaluator</label>
                                <select x-model="evalItem.evaluator_user_id"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">— Select Evaluator —</option>
                                    <template x-for="m in members" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <button x-show="evaluations.length > 1" type="button" @click="removeEvaluation(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0" title="Remove evaluation">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addEvaluation()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Evaluation
            </button>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('meetings.show', $meeting) }}" class="px-5 py-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors">Cancel</a>
            <button type="submit" wire:loading.attr="disabled" class="px-7 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 disabled:opacity-60 active:scale-[0.98]">
                <span wire:loading.remove>Save Changes</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Saving…
                </span>
            </button>
        </div>

    </form>
</div>
