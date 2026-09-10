<div class="p-6 lg:p-8">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('meetings.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Meeting</h1>
                @if($club)
                <p class="text-sm text-gray-500 mt-1">
                    Creating for <span class="font-medium text-indigo-600">{{ $club->name }}</span>
                </p>
                @endif
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">

            {{-- Validation errors summary --}}
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm font-medium text-red-700 mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ================================================================ --}}
            {{-- Meeting Information --}}
            {{-- ================================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Meeting Information</h2>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting Date <span class="text-red-500">*</span></label>
                            <input wire:model="meeting_date" type="date"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('meeting_date') border-red-300 @enderror">
                            @error('meeting_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting Number <span class="text-red-500">*</span></label>
                            <input wire:model="meeting_number" type="number" min="1"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('meeting_number') border-red-300 @enderror">
                            @error('meeting_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Theme</label>
                        <input wire:model="theme" type="text" placeholder="e.g. The Art of Procrastination"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Venue</label>
                            <input wire:model="venue" type="text" placeholder="e.g. Conference Room A"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select wire:model="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="notes" rows="3" placeholder="Any additional notes…"
                                  class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- ================================================================ --}}
            {{-- Fixed Meeting Roles --}}
            {{-- ================================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Meeting Roles</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach($roleTypes as $roleType)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $roleType->name }}</label>
                        <select wire:model="roleAssignments.{{ $roleType->id }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Select Member —</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ================================================================ --}}
            {{-- Prepared Speakers --}}
            {{-- ================================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Prepared Speakers</h2>
                <div class="space-y-4">
                    @foreach($speakers as $index => $speaker)
                    <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-xl">
                        <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-indigo-600 text-xs font-bold">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Speaker</label>
                                <select wire:model="speakers.{{ $index }}.user_id"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="">— Select Member —</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Topic</label>
                                <input wire:model="speakers.{{ $index }}.topic" type="text" placeholder="Speech topic"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Speech Type</label>
                                <input wire:model="speakers.{{ $index }}.speech_type" type="text" placeholder="e.g. Prepared, Ice Breaker"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Duration</label>
                                <input wire:model="speakers.{{ $index }}.duration" type="text" placeholder="e.g. 5-7 min"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                        </div>
                        @if(count($speakers) > 1)
                        <button type="button" wire:click="removeSpeaker({{ $index }})"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addSpeaker"
                        class="mt-4 flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Speaker
                </button>
            </div>

            {{-- ================================================================ --}}
            {{-- TTM Speakers --}}
            {{-- ================================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Table Topics (TTM) Speakers</h2>
                <div class="space-y-4">
                    @foreach($ttmSpeakers as $index => $ttm)
                    <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-xl">
                        <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-purple-600 text-xs font-bold">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">TTM Speaker</label>
                                <select wire:model="ttmSpeakers.{{ $index }}.user_id"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="">— Select Member —</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Topic</label>
                                <input wire:model="ttmSpeakers.{{ $index }}.topic" type="text" placeholder="TTM topic"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                        </div>
                        @if(count($ttmSpeakers) > 1)
                        <button type="button" wire:click="removeTtmSpeaker({{ $index }})"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addTtmSpeaker"
                        class="mt-4 flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add TTM Speaker
                </button>
            </div>

            {{-- ================================================================ --}}
            {{-- Evaluators --}}
            {{-- ================================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Evaluators</h2>
                <div class="space-y-4">
                    @foreach($evaluations as $index => $evaluation)
                    <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-xl">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Speaker Being Evaluated</label>
                                <select wire:model="evaluations.{{ $index }}.speaker_index"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    @foreach($speakers as $si => $s)
                                        <option value="{{ $si }}">Speaker {{ $si + 1 }}{{ $s['user_id'] ? ' — ' . ($members->firstWhere('id', $s['user_id'])?->name ?? '') : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Evaluator</label>
                                <select wire:model="evaluations.{{ $index }}.evaluator_user_id"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="">— Select Evaluator —</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if(count($evaluations) > 1)
                        <button type="button" wire:click="removeEvaluation({{ $index }})"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addEvaluation"
                        class="mt-4 flex items-center gap-2 text-sm text-green-600 hover:text-green-800 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Evaluation
                </button>
            </div>

            {{-- ================================================================ --}}
            {{-- Actions --}}
            {{-- ================================================================ --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('meetings.index') }}" class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">Cancel</a>
                <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm disabled:opacity-60">
                    <span wire:loading.remove>Create Meeting</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Creating…
                    </span>
                </button>
            </div>

        </form>
    </div>
</div>
