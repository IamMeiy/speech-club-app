<div class="max-w-6xl mx-auto space-y-6"
     x-data="{
         currentTheme: localStorage.getItem('theme_color') || 'indigo',
         showLiveTools: false,
         showSpeakerModal: false,
         showTtmModal: false,
         activeTab: 'ah_counter',
         searchAttendee: '',
         ahCounts: {{ !empty($initialAhLogs) ? Js::from($initialAhLogs) : '{}' }},
         grammarCounts: {{ !empty($initialGrammarLogs) ? Js::from($initialGrammarLogs) : '{}' }},
         isSavingCounts: false,
         canManageTimer: {{ $canManageTimer ? 'true' : 'false' }},
         canManageAhCounter: {{ $canManageAhCounter ? 'true' : 'false' }},
         canManageGrammarian: {{ $canManageGrammarian ? 'true' : 'false' }},
         canManageListeningMaster: {{ $canManageListeningMaster ? 'true' : 'false' }},
         canManageMinutes: {{ $canManageMinutes ? 'true' : 'false' }},
         showListeningMasterModal: false,
         showMinutesModal: false,

         getAh(userId, type) {
             if (!this.ahCounts[userId]) {
                 this.ahCounts[userId] = { ah_count: 0, um_count: 0, er_count: 0, like_count: 0, you_know_count: 0, so_count: 0, repeats_count: 0, other_count: 0 };
             }
             return this.ahCounts[userId][type] || 0;
         },

         getTotal(userId) {
             const c = this.ahCounts[userId];
             if (!c) return 0;
             return (c.ah_count || 0) + (c.um_count || 0) + (c.er_count || 0) + (c.like_count || 0) + (c.you_know_count || 0) + (c.so_count || 0) + (c.repeats_count || 0) + (c.other_count || 0);
         },

         incrementAh(userId, type) {
             if (!this.canManageAhCounter) return;
             if (!this.ahCounts[userId]) {
                 this.ahCounts[userId] = { ah_count: 0, um_count: 0, er_count: 0, like_count: 0, you_know_count: 0, so_count: 0, repeats_count: 0, other_count: 0 };
             }
             this.ahCounts[userId][type] = (this.ahCounts[userId][type] || 0) + 1;
         },

         decrementAh(userId, type) {
             if (!this.canManageAhCounter) return;
             if (!this.ahCounts[userId]) return;
             if (this.ahCounts[userId][type] > 0) {
                 this.ahCounts[userId][type]--;
             }
         },

         getWod(userId) {
             return this.grammarCounts[userId]?.word_of_day_count || 0;
         },

         incrementWod(userId) {
             if (!this.canManageGrammarian) return;
             if (!this.grammarCounts[userId]) {
                 this.grammarCounts[userId] = { word_of_day_count: 0, good_phrases: '', awkward_phrases: '', notes: '' };
             }
             this.grammarCounts[userId].word_of_day_count = (this.grammarCounts[userId].word_of_day_count || 0) + 1;
         },

         decrementWod(userId) {
             if (!this.canManageGrammarian) return;
             if (!this.grammarCounts[userId]) return;
             if (this.grammarCounts[userId].word_of_day_count > 0) {
                 this.grammarCounts[userId].word_of_day_count--;
             }
         },

         saveFinalCounts(role = null) {
             const targetRole = role || this.activeTab;
             if (targetRole === 'ah_counter' && !this.canManageAhCounter) return;
             if (targetRole === 'grammarian' && !this.canManageGrammarian) return;
             this.isSavingCounts = true;

             let cleanAh = null;
             let cleanGrammar = null;

             if (targetRole === 'ah_counter' || targetRole === 'all') {
                 cleanAh = {};
                 for (const [uid, val] of Object.entries(this.ahCounts || {})) {
                     const id = parseInt(uid, 10);
                     if (id > 0 && typeof val === 'object' && val !== null) {
                         cleanAh[id] = val;
                     }
                 }
             }

             if (targetRole === 'grammarian' || targetRole === 'all') {
                 cleanGrammar = {};
                 for (const [uid, val] of Object.entries(this.grammarCounts || {})) {
                     const id = parseInt(uid, 10);
                     if (id > 0) {
                         cleanGrammar[id] = val;
                     }
                 }
             }

             $wire.saveAllCounts(cleanAh, cleanGrammar, targetRole).then(() => {
                 this.isSavingCounts = false;
                 this.showLiveTools = false;
             }).catch(() => {
                 this.isSavingCounts = false;
             });
         },

          showGrammarModal: false,
          grammarModalUserId: null,
          grammarModalUserName: '',
          grammarModalGoodPhrases: '',
          grammarModalAwkwardPhrases: '',

          openGrammarNotes(userId, userName) {
              this.grammarModalUserId = userId;
              this.grammarModalUserName = userName;
              if (!this.grammarCounts[userId]) {
                  this.grammarCounts[userId] = { word_of_day_count: 0, good_phrases: '', awkward_phrases: '', notes: '' };
              }
              this.grammarModalGoodPhrases = this.grammarCounts[userId].good_phrases || '';
              this.grammarModalAwkwardPhrases = this.grammarCounts[userId].awkward_phrases || '';
              this.showGrammarModal = true;
          },

          saveGrammarNotesModal() {
              if (!this.canManageGrammarian) {
                  this.showGrammarModal = false;
                  return;
              }
              if (this.grammarModalUserId) {
                  if (!this.grammarCounts[this.grammarModalUserId]) {
                      this.grammarCounts[this.grammarModalUserId] = { word_of_day_count: 0, good_phrases: '', awkward_phrases: '', notes: '' };
                  }
                  this.grammarCounts[this.grammarModalUserId].good_phrases = this.grammarModalGoodPhrases;
                  this.grammarCounts[this.grammarModalUserId].awkward_phrases = this.grammarModalAwkwardPhrases;
              }
              this.showGrammarModal = false;
          },

          getGoodPhrases(userId) {
              return this.grammarCounts[userId]?.good_phrases || '';
          },

          getAwkwardPhrases(userId) {
              return this.grammarCounts[userId]?.awkward_phrases || '';
          },

          matchesSearch(name, roles = '') {
              if (!this.searchAttendee.trim()) return true;
              const q = this.searchAttendee.toLowerCase().trim();
              return name.toLowerCase().includes(q) || roles.toLowerCase().includes(q);
          },

          // Evaluation Notes Modal (Pure Alpine.js 0ms latency)
          showEvalNotesModal: false,
          evalModalId: null,
          evalModalSpeakerName: '',
          evalModalEvaluatorName: '',
          evalModalSpeechTitle: '',
          evalModalNotes: '',
          evalModalCanEdit: false,
          evalModalSaving: false,

          openEvalModal(id, speakerName, evaluatorName, speechTitle, notes, canEdit) {
              this.evalModalId = id;
              this.evalModalSpeakerName = speakerName;
              this.evalModalEvaluatorName = evaluatorName;
              this.evalModalSpeechTitle = speechTitle;
              this.evalModalNotes = notes || '';
              this.evalModalCanEdit = canEdit;
              this.showEvalNotesModal = true;
          },

          saveEvalNotes() {
              if (!this.evalModalId) return;
              this.evalModalSaving = true;
              $wire.saveEvaluationNotes(this.evalModalId, this.evalModalNotes).then(() => {
                  this.evalModalSaving = false;
                  this.showEvalNotesModal = false;
              }).catch(() => {
                  this.evalModalSaving = false;
              });
          },

          // Standalone Timer Sheet State
          showTimerSheet: false,
          timerTab: 'speakers',
          timerLogs: {{ !empty($initialTimerLogs) ? Js::from($initialTimerLogs) : '{}' }},
          isSavingTimer: false,

          getTimer(key, field) {
              if (!this.timerLogs[key]) {
                  this.timerLogs[key] = { time_taken: '', status: 'within_time', notes: '' };
              }
              return this.timerLogs[key][field] ?? '';
          },

          setTimer(key, field, val) {
              if (!this.canManageTimer) return;
              if (!this.timerLogs[key]) {
                  this.timerLogs[key] = { time_taken: '', status: 'within_time', notes: '' };
              }
              this.timerLogs[key][field] = val;
          },

          // Unified Interactive Timepicker Popover State
          activeTimePicker: null, // { key, name, allotted, type }
          pickerMin: '00',
          pickerSec: '00',

          openTimePicker(key, name, allotted, type) {
              if (!this.canManageTimer) return;
              this.activeTimePicker = { key, name, allotted, type };
              const current = this.getTimer(key, 'time_taken');
              if (current && typeof current === 'string' && current.includes(':')) {
                  const parts = current.split(':');
                  this.pickerMin = parts[0].padStart(2, '0');
                  this.pickerSec = parts[1].padStart(2, '0');
              } else {
                  this.pickerMin = '00';
                  this.pickerSec = '00';
              }
          },

          selectPickerMin(m) {
              this.pickerMin = m.toString().padStart(2, '0');
              this.applyPickerTime();
          },

          selectPickerSec(s) {
              this.pickerSec = s.toString().padStart(2, '0');
              this.applyPickerTime();
          },

          selectPickerPreset(preset) {
              if (preset && preset.includes(':')) {
                  const parts = preset.split(':');
                  this.pickerMin = parts[0].padStart(2, '0');
                  this.pickerSec = parts[1].padStart(2, '0');
                  this.applyPickerTime();
              }
          },

          applyPickerTime() {
              if (this.activeTimePicker) {
                  this.setTimer(this.activeTimePicker.key, 'time_taken', `${this.pickerMin}:${this.pickerSec}`);
              }
          },

          clearPickerTime() {
              if (this.activeTimePicker) {
                  this.setTimer(this.activeTimePicker.key, 'time_taken', '');
                  this.pickerMin = '00';
                  this.pickerSec = '00';
              }
              this.activeTimePicker = null;
          },

          closeTimePicker() {
              this.activeTimePicker = null;
          },

          saveAllTimerLogs() {
              if (!this.canManageTimer) return;
              this.isSavingTimer = true;
              const payload = [];

              @foreach($meeting->speakers as $sp)
              payload.push({
                  speaker_type: 'prepared_speaker',
                  reference_id: {{ $sp->id }},
                  user_id: {{ $sp->user_id ?? 'null' }},
                  allotted_time: '{{ addslashes($sp->formattedTiming()) }}',
                  time_taken: this.getTimer('prepared_speaker_{{ $sp->id }}', 'time_taken'),
                  status: this.getTimer('prepared_speaker_{{ $sp->id }}', 'status') || 'within_time',
                  notes: this.getTimer('prepared_speaker_{{ $sp->id }}', 'notes'),
              });
              @endforeach

              @foreach($meeting->evaluations as $ev)
              payload.push({
                  speaker_type: 'evaluator',
                  reference_id: {{ $ev->id }},
                  user_id: {{ $ev->evaluator_user_id ?? 'null' }},
                  allotted_time: '2-3 mins',
                  time_taken: this.getTimer('evaluator_{{ $ev->id }}', 'time_taken'),
                  status: this.getTimer('evaluator_{{ $ev->id }}', 'status') || 'within_time',
                  notes: this.getTimer('evaluator_{{ $ev->id }}', 'notes'),
              });
              @endforeach

              @foreach($meeting->ttmSpeakers as $ttm)
              payload.push({
                  speaker_type: 'ttm_speaker',
                  reference_id: {{ $ttm->id }},
                  user_id: {{ $ttm->user_id ?? 'null' }},
                  allotted_time: '1-2 mins',
                  time_taken: this.getTimer('ttm_speaker_{{ $ttm->id }}', 'time_taken'),
                  status: this.getTimer('ttm_speaker_{{ $ttm->id }}', 'status') || 'within_time',
                  notes: this.getTimer('ttm_speaker_{{ $ttm->id }}', 'notes'),
              });
              @endforeach

              $wire.saveTimerLogs(payload).then(() => {
                  this.isSavingTimer = false;
                  this.showTimerSheet = false;
              }).catch(() => {
                  this.isSavingTimer = false;
              });
          }
      }"
      @speaker-signed-up.window="showSpeakerModal = false"
      @ttm-signed-up.window="showTtmModal = false"
      @minutes-of-meeting-saved.window="showMinutesModal = false"
      @keydown.escape.window="if (showMinutesModal) { showMinutesModal = false; } else if (showListeningMasterModal) { showListeningMasterModal = false; } else if (showTimerSheet) { showTimerSheet = false; } else if (showEvalNotesModal) { showEvalNotesModal = false; } else if (showGrammarModal) { showGrammarModal = false; } else if (showLiveTools) { showLiveTools = false; } else { showSpeakerModal = false; showTtmModal = false; }">

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-7 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors space-y-5">
        {{-- Top Row: Identity & Primary Management --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('meetings.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Meeting #{{ $meeting->meeting_number }}</h1>
                        @if($canUpdateStatus)
                        {{-- Interactive Status Dropdown --}}
                        <div class="relative" x-data="{
                            openStatus: false,
                            currentStatus: '{{ $meeting->status }}',
                            changeStatus(status) {
                                this.openStatus = false;
                                if (status === this.currentStatus) return;
                                
                                if (status === 'completed') {
                                    $confirm({
                                        title: 'Complete & Lock Meeting',
                                        message: 'Marking this meeting as Completed will finalize speech credits, attendance records, and lock live counter tools. Are you sure?',
                                        type: 'warning',
                                        confirmText: 'Mark Completed',
                                        onConfirm: () => {
                                            this.currentStatus = 'completed';
                                            $wire.updateStatus('completed');
                                        }
                                    });
                                } else if (status === 'cancelled') {
                                    $confirm({
                                        title: 'Cancel Meeting',
                                        message: 'Are you sure you want to cancel this meeting? Members will not receive speech credit for cancelled meetings.',
                                        type: 'danger',
                                        confirmText: 'Cancel Meeting',
                                        onConfirm: () => {
                                            this.currentStatus = 'cancelled';
                                            $wire.updateStatus('cancelled');
                                        }
                                    });
                                } else {
                                    this.currentStatus = status;
                                    $wire.updateStatus(status);
                                }
                            }
                        }" @click.outside="openStatus = false">
                            <button type="button" @click="openStatus = !openStatus"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $meeting->statusColor() }} hover:ring-2 hover:ring-primary-500/30 transition-all cursor-pointer shadow-xs"
                                    title="Click to change status">
                                <span>{{ ucfirst($meeting->status) }}</span>
                                <svg class="w-3.5 h-3.5 opacity-70 transition-transform duration-150" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openStatus"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl py-1.5 z-30"
                                 style="display: none;">
                                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Change Status</div>
                                @foreach(['draft' => 'Draft', 'scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $stKey => $stLabel)
                                    <button type="button" @click="changeStatus('{{ $stKey }}')"
                                            class="w-full flex items-center justify-between px-3 py-1.5 text-xs font-medium transition-colors text-left"
                                            :class="currentStatus === '{{ $stKey }}' ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60'">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $stKey === 'completed' ? 'bg-emerald-500' : ($stKey === 'scheduled' ? 'bg-blue-500' : ($stKey === 'cancelled' ? 'bg-rose-500' : 'bg-slate-400')) }}"></span>
                                            <span>{{ $stLabel }}</span>
                                        </div>
                                        <template x-if="currentStatus === '{{ $stKey }}'">
                                            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $meeting->statusColor() }}">{{ ucfirst($meeting->status) }}</span>
                        @endif
                        @if($isMeetingLocked)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Locked</span>
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                        {{ $meeting->club?->name ?? 'Speech Club' }} &bull; {{ $meeting->meeting_date->format('d F Y') }}
                        @if($meeting->formattedTime())
                            &bull; <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $meeting->formattedTime() }}</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Primary Admin Actions --}}
            <div class="flex items-center gap-2 self-start sm:self-auto">
                @can('meetings.update')
                <a href="{{ route('meetings.edit', $meeting) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold rounded-2xl transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Edit</span>
                </a>
                @endcan

                @can('attendance.manage')
                <a href="{{ route('meetings.attendance', $meeting) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Attendance</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- Bottom Action Strip: Tools & Outputs --}}
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            {{-- Left: Session Facilitation Tools --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1 flex items-center gap-1.5 flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Session Tools:
                </span>

                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    {{-- Live Facilitator Tools Button --}}
                    <button @click="showTimerSheet = false; showListeningMasterModal = false; showLiveTools = true; activeTab = 'ah_counter'" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300/60 dark:border-amber-700/50 text-xs font-semibold rounded-2xl transition-all shadow-xs active:scale-[0.98]">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                        <span>Live Counters</span>
                    </button>

                    {{-- Standalone Timer Sheet Button --}}
                    <button @click="showLiveTools = false; showListeningMasterModal = false; showTimerSheet = true" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-300/60 dark:border-indigo-700/50 text-xs font-semibold rounded-2xl transition-all shadow-xs active:scale-[0.98]">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Timer Sheet</span>
                    </button>

                    {{-- Listening Master Report Button --}}
                    <button @click="showLiveTools = false; showTimerSheet = false; showListeningMasterModal = true" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-300/60 dark:border-purple-700/50 text-xs font-semibold rounded-2xl transition-all shadow-xs active:scale-[0.98]">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                        <span>Listening Master</span>
                    </button>
                </div>
            </div>

            {{-- Right: Meeting Outputs & Reports --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1 flex-shrink-0">Outputs:</span>

                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    {{-- Agenda PDF Download --}}
                    <button x-on:click="$wire.downloadAgenda(currentTheme)" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-2xl transition-all shadow-xs active:scale-[0.98]">
                        <svg wire:loading.remove wire:target="downloadAgenda" class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <svg wire:loading wire:target="downloadAgenda" class="w-4 h-4 animate-spin text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="downloadAgenda">Agenda PDF</span>
                        <span wire:loading wire:target="downloadAgenda">Generating…</span>
                    </button>

                    {{-- Meeting Report --}}
                    @can('reports.view')
                    <a href="{{ route('meetings.report', $meeting) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-2xl transition-all shadow-xs active:scale-[0.98]">
                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Meeting Report</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Word of the Day Banner --}}
    @if($meeting->hasWordOfTheDay())
    <div class="bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-transparent dark:from-amber-950/30 dark:via-amber-900/10 border border-amber-200 dark:border-amber-800/60 rounded-3xl p-6 sm:p-7 shadow-sm transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 dark:bg-amber-400/15 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">Word of the Day</span>
                        @if($meeting->word_part_of_speech)
                            <span class="text-[10px] font-semibold italic text-amber-700 dark:text-amber-300 bg-amber-200/60 dark:bg-amber-900/60 px-2 py-0.5 rounded-md">
                                {{ $meeting->word_part_of_speech }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-amber-950 dark:text-amber-200 tracking-tight mt-0.5">
                        {{ $meeting->word_of_the_day }}
                    </h2>
                    @if($meeting->word_definition)
                        <p class="text-xs sm:text-sm text-amber-900/80 dark:text-amber-300/90 mt-1 font-medium leading-relaxed">
                            {{ $meeting->word_definition }}
                        </p>
                    @endif
                    @if($meeting->word_example_sentence)
                        <p class="text-xs text-amber-800/70 dark:text-amber-400/80 mt-1 italic">
                            &ldquo;{{ $meeting->word_example_sentence }}&rdquo;
                        </p>
                    @endif
                </div>
            </div>
            <div class="self-start sm:self-center flex-shrink-0">
                <button @click="showTimerSheet = false; showLiveTools = true; activeTab = 'grammarian'" type="button"
                        class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all active:scale-[0.98]">
                    <span x-text="canManageGrammarian ? 'Tally Word Usage →' : 'View Word Usage →'"></span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Meeting Info Card --}}
    @if($meeting->theme || $meeting->venue || $meeting->notes || $meeting->start_time)
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @if($meeting->theme)
            <div class="lg:col-span-2">
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Theme</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">&ldquo;{{ $meeting->theme }}&rdquo;</p>
            </div>
            @endif
            @if($meeting->formattedTime())
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Meeting Time</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">{{ $meeting->formattedTime() }}</p>
            </div>
            @endif
            @if($meeting->venue)
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Venue</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">{{ $meeting->venue }}</p>
            </div>
            @endif
        </div>
        @if($meeting->notes)
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Meeting Agenda & Announcements</p>
            <div class="rich-text-content text-slate-600 dark:text-slate-300">
                {!! $meeting->notes !!}
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ================================================================ --}}
    {{-- Minutes of Meeting (MoM) Card --}}
    {{-- ================================================================ --}}
    <div id="minutes-of-meeting" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Minutes of Meeting (MoM)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Official record of proceedings, club business, motions, and executive reports.</p>
                </div>
            </div>

            @if($canManageMinutes)
            <button @click="showMinutesModal = true" type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/60 dark:hover:bg-primary-900/60 text-primary-700 dark:text-primary-300 border border-primary-200/70 dark:border-primary-800/60 rounded-xl text-xs font-semibold transition-all active:scale-95 self-start sm:self-auto">
                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span>{{ $meeting->minutes_of_meeting ? 'Edit Minutes' : '+ Add Minutes' }}</span>
            </button>
            @endif
        </div>

        <div class="mt-5">
            @if($meeting->minutes_of_meeting)
                <div class="rich-text-content text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                    {!! $meeting->minutes_of_meeting !!}
                </div>
            @else
                <div class="py-8 text-center flex flex-col items-center justify-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No Minutes of Meeting recorded yet</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">The club secretary or meeting officers can document proceedings here.</p>
                    @if($canManageMinutes)
                    <button @click="showMinutesModal = true" type="button"
                            class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Minutes of Meeting</span>
                    </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ================================================================ --}}
        {{-- Meeting Roles (Self-Service Signups) --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between pb-3 mb-5 border-b border-slate-100 dark:border-slate-800 gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white whitespace-nowrap">Meeting Roles</h2>
                @if($canVolunteer)
                    <span class="text-xs text-primary-600 dark:text-primary-400 font-semibold flex items-center gap-1 whitespace-nowrap flex-shrink-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Signups Open
                    </span>
                @endif
            </div>

            <div class="space-y-3.5">
                @foreach($allRoleTypes as $roleType)
                    @php
                        $assignedRole = $meeting->roles->firstWhere('meeting_role_type_id', $roleType->id);
                        $isCurrentUser = $assignedRole && $assignedRole->user_id === $currentUserId;
                    @endphp
                    <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-slate-800/60 last:border-0 gap-3">
                        <span class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 w-32 sm:w-44 truncate flex-shrink-0">
                            {{ $roleType->name }}
                        </span>

                        @if($assignedRole && $assignedRole->user)
                            <div class="flex items-center gap-2 min-w-0 justify-end flex-1">
                                <div class="w-7 h-7 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($assignedRole->user->name, 0, 1)) }}
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white truncate">
                                    {{ $assignedRole->user->name }}
                                </span>

                                @if($isCurrentUser)
                                    <span class="text-[10px] font-extrabold bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-md whitespace-nowrap flex-shrink-0">
                                        You
                                    </span>
                                @endif

                                @if($canVolunteer && ($isCurrentUser || auth()->user()->can('meetings.update')))
                                    <button type="button"
                                            x-data
                                            @click="$confirm({
                                                title: 'Step Down from Role',
                                                message: 'Are you sure you want to step down from this role?',
                                                type: 'warning',
                                                confirmText: 'Step Down',
                                                onConfirm: () => $wire.relinquishRole({{ $assignedRole->id }})
                                            })"
                                            class="text-[11px] font-semibold text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 ml-1 transition-colors whitespace-nowrap flex-shrink-0">
                                        Step Down
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs text-slate-400 dark:text-slate-500 italic whitespace-nowrap flex-shrink-0">Vacant</span>
                                @if($canVolunteer)
                                    <button wire:click="signUpForRole({{ $roleType->id }})"
                                            class="px-2.5 py-1 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/50 dark:hover:bg-primary-900/60 text-primary-600 dark:text-primary-400 border border-primary-200/80 dark:border-primary-800/60 rounded-xl text-xs font-semibold transition-all active:scale-95 whitespace-nowrap flex-shrink-0">
                                        + Volunteer
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Prepared Speakers --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between pb-3 mb-5 border-b border-slate-100 dark:border-slate-800 gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white whitespace-nowrap">Prepared Speakers</h2>
                @if($canVolunteer)
                    <button @click="showSpeakerModal = true" type="button"
                            class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all active:scale-95 flex items-center gap-1.5 whitespace-nowrap flex-shrink-0">
                        <span>+ Sign Up as Speaker</span>
                    </button>
                @endif
            </div>

            @if($meeting->speakers->isEmpty())
                <div class="text-center py-8">
                    <p class="text-slate-400 dark:text-slate-500 text-sm">No speakers registered yet.</p>
                    @if($canVolunteer)
                        <button @click="showSpeakerModal = true" type="button" class="mt-2 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            Be the first to deliver a speech →
                        </button>
                    @endif
                </div>
            @else
            <div class="space-y-3">
                @foreach($meeting->speakers as $speaker)
                @php
                    $isMySpeech = $speaker->user_id === $currentUserId;
                @endphp
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <span class="text-xs font-extrabold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-2.5 py-0.5 rounded-full flex-shrink-0">#{{ $speaker->slot }}</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $speaker->user?->name ?? 'Speaker' }}</span>
                                @if($isMySpeech)
                                    <span class="text-[10px] font-extrabold bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-md whitespace-nowrap flex-shrink-0">You</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs text-slate-400 font-semibold bg-white dark:bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-200/60 dark:border-slate-700 whitespace-nowrap flex-shrink-0">{{ $speaker->duration ?: '5-7 mins' }}</span>
                                @if($canVolunteer && ($isMySpeech || auth()->user()->can('meetings.update')))
                                    <button type="button"
                                            x-data
                                            @click="$confirm({
                                                title: 'Remove Speaker Slot',
                                                message: 'Are you sure you want to remove this speaker slot?',
                                                type: 'warning',
                                                confirmText: 'Remove',
                                                onConfirm: () => $wire.relinquishSpeaker({{ $speaker->id }})
                                            })"
                                            class="text-[11px] font-semibold text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 transition-colors whitespace-nowrap flex-shrink-0">
                                        Remove
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($speaker->topic)
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-2 ml-8">&ldquo;{{ $speaker->topic }}&rdquo;</p>
                        @endif

                        <div class="flex flex-wrap items-center gap-2 ml-8 mt-1.5 text-[11px]">
                            @if($speaker->projectModel)
                                <span class="font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-md border border-primary-200/60 dark:border-primary-900/40">
                                    {{ $speaker->projectModel->levelBadge() }}: {{ $speaker->projectModel->name }}
                                </span>
                            @elseif($speaker->project)
                                <span class="font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">
                                    {{ $speaker->project }}
                                </span>
                            @endif

                            @if($speaker->speech_type)
                                <span class="text-slate-400 dark:text-slate-500">&bull; {{ $speaker->speech_type }}</span>
                            @endif
                        </div>

                        @if($speaker->evaluation)
                            @php
                                $eval = $speaker->evaluation;
                                $canEditEval = ! $isMeetingLocked && (($eval->evaluator_user_id === $currentUserId) || auth()->user()->can('meetings.update') || auth()->user()->isSuperAdmin());
                                $hasNotes = !empty(trim($eval->notes ?? ''));
                            @endphp
                            <div class="mt-3 ml-8 p-3.5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-700/60 shadow-xs space-y-2">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            E
                                        </div>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">
                                            Evaluator: <span class="font-bold text-slate-900 dark:text-white">{{ $eval->evaluator?->name ?? 'Evaluator' }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if($hasNotes)
                                            <button type="button"
                                                    @click="openEvalModal({{ $eval->id }}, '{{ addslashes($speaker->user?->name ?? 'Speaker') }}', '{{ addslashes($eval->evaluator?->name ?? 'Evaluator') }}', '{{ addslashes($speaker->topic ?: 'Speech #' . $speaker->slot) }}', {{ Js::from($eval->notes) }}, {{ $canEditEval ? 'true' : 'false' }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Read Feedback</span>
                                            </button>
                                        @endif
                                        @if($canEditEval)
                                            <button type="button"
                                                    @click="openEvalModal({{ $eval->id }}, '{{ addslashes($speaker->user?->name ?? 'Speaker') }}', '{{ addslashes($eval->evaluator?->name ?? 'Evaluator') }}', '{{ addslashes($speaker->topic ?: 'Speech #' . $speaker->slot) }}', {{ Js::from($eval->notes ?? '') }}, true)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>{{ $hasNotes ? 'Edit Notes' : '+ Write Feedback' }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if($hasNotes)
                                    <div class="text-xs text-slate-700 dark:text-slate-300 italic bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/70 dark:border-slate-800 leading-relaxed break-words whitespace-pre-line">
                                        &ldquo;{{ trim($eval->notes) }}&rdquo;
                                    </div>
                                @elseif(! $canEditEval)
                                    <p class="text-[11px] text-slate-400 italic">No written evaluation notes posted yet.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- Table Topics Speakers --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between pb-3 mb-5 border-b border-slate-100 dark:border-slate-800 gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white whitespace-nowrap">Table Topics Speakers</h2>
                @if($canVolunteer)
                    <button @click="showTtmModal = true" type="button"
                            class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all active:scale-95 flex items-center gap-1.5 whitespace-nowrap flex-shrink-0">
                        <span>+ Volunteer for TTM</span>
                    </button>
                @endif
            </div>

            @if($meeting->ttmSpeakers->isEmpty())
                <div class="text-center py-8">
                    <p class="text-slate-400 dark:text-slate-500 text-sm">No Table Topics participants signed up yet.</p>
                    @if($canVolunteer)
                        <button @click="showTtmModal = true" type="button" class="mt-2 text-xs font-semibold text-purple-600 dark:text-purple-400 hover:underline">
                            Volunteer for impromptu speaking →
                        </button>
                    @endif
                </div>
            @else
            <div class="space-y-3">
                @foreach($meeting->ttmSpeakers as $ttm)
                @php
                    $isMyTtm = $ttm->user_id === $currentUserId;
                @endphp
                <div class="flex items-center justify-between py-2.5 border-b border-slate-50 dark:border-slate-800/60 last:border-0 gap-3">
                    <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                        <span class="text-xs font-extrabold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full flex-shrink-0 mt-0.5 sm:mt-0">#{{ $ttm->slot }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">{{ $ttm->user?->name ?? 'Participant' }}</p>
                                @if($isMyTtm)
                                    <span class="text-[10px] font-extrabold bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md whitespace-nowrap flex-shrink-0">You</span>
                                @endif
                            </div>
                            @if($ttm->topic) <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 break-words">&ldquo;{{ $ttm->topic }}&rdquo;</p> @endif
                        </div>
                    </div>
                    @if($canVolunteer && ($isMyTtm || auth()->user()->can('meetings.update')))
                        <button type="button"
                                x-data
                                @click="$confirm({
                                    title: 'Step Down from Table Topics',
                                    message: 'Are you sure you want to remove your Table Topics participation?',
                                    type: 'warning',
                                    confirmText: 'Step Down',
                                    onConfirm: () => $wire.relinquishTtm({{ $ttm->id }})
                                })"
                                class="text-[11px] font-semibold text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 transition-colors whitespace-nowrap flex-shrink-0 ml-2">
                            Step Down
                        </button>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Attendance Overview --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Attendance Overview</h2>
            @if($meeting->attendance->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">Attendance not recorded yet.</p>
                @can('attendance.manage')
                <a href="{{ route('meetings.attendance', $meeting) }}" class="mt-3 inline-flex items-center text-xs sm:text-sm text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                    Record attendance →
                </a>
                @endcan
            @else
            @php
                $present = $meeting->attendance->where('status', 'present')->count();
                $absent  = $meeting->attendance->where('status', 'absent')->count();
                $late    = $meeting->attendance->where('status', 'late')->count();
                $total   = $meeting->attendance->count();
            @endphp
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $present }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Present</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-rose-500">{{ $absent }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Absent</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-amber-500">{{ $late }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Late</p>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $total > 0 ? ($present / $total * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 font-medium">{{ $present }} of {{ $total }} members attended</p>
            @endif
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- Volunteer Speaker Modal with Project Selection --}}
    {{-- ================================================================ --}}
    {{-- ================================================================ --}}
    {{-- Volunteer Speaker Modal with Project Selection (Alpine.js) --}}
    {{-- ================================================================ --}}
    <div x-show="showSpeakerModal"
         x-cloak
         x-data="{
             projectsList: {{ Js::from($projects->map(fn($p) => [
                 'id' => $p->id,
                 'name' => $p->name,
                 'badge' => $p->levelBadge(),
                 'track' => $p->track ?: '',
                 'duration' => $p->formattedTiming(),
                 'speech_type' => $p->track ?: 'Speech Project'
             ])) }},
             openProj: false,
             searchProj: '',
             isCatalogProject: false,
             get filteredProjects() {
                 if (!this.searchProj.trim()) return this.projectsList;
                 const q = this.searchProj.toLowerCase();
                 return this.projectsList.filter(p =>
                     p.name.toLowerCase().includes(q) ||
                     (p.track && p.track.toLowerCase().includes(q)) ||
                     (p.badge && p.badge.toLowerCase().includes(q)) ||
                     (p.duration && p.duration.toLowerCase().includes(q))
                 );
             },
             get selectedProject() {
                 return this.projectsList.find(p => String(p.id) === String($wire.speakerProjectId));
             },
             get displayText() {
                 const p = this.selectedProject;
                 if (!p) return '— Select from Speech Catalog (Searchable) —';
                 return `[${p.badge}] ${p.name} (${p.duration})`;
             },
             choose(id) {
                 this.openProj = false;
                 this.searchProj = '';
                 const p = this.projectsList.find(item => String(item.id) === String(id));
                 if (p) {
                     this.isCatalogProject = true;
                     $wire.set('speakerProjectId', p.id);
                     $wire.set('speakerProject', p.name);
                     $wire.set('speakerDuration', p.duration);
                     $wire.set('speakerSpeechType', p.speech_type);
                 } else {
                     this.isCatalogProject = false;
                     $wire.set('speakerProjectId', null);
                     $wire.set('speakerProject', '');
                     $wire.set('speakerDuration', '5-7 mins');
                     $wire.set('speakerSpeechType', 'Speech Project');
                 }
             },
             resetSpeakerForm() {
                 this.isCatalogProject = false;
                 this.openProj = false;
                 this.searchProj = '';
                 $wire.set('speakerProjectId', null);
                 $wire.set('speakerProject', '');
                 $wire.set('speakerDuration', '5-7 mins');
                 $wire.set('speakerSpeechType', 'Speech Project');
                 $wire.set('speakerTopic', '');
             }
         }"
         @speaker-signed-up.window="resetSpeakerForm(); showSpeakerModal = false"
         @click.self="resetSpeakerForm(); showSpeakerModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Volunteer as Prepared Speaker</h3>
                <button @click="resetSpeakerForm(); showSpeakerModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="signUpAsSpeaker" class="space-y-4">
                {{-- Searchable Project Selector --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Speech Project</label>
                    <div class="relative" @click.outside="openProj = false; searchProj = ''">
                        <button type="button" @click="openProj = !openProj; if(openProj) $nextTick(() => $refs.searchInp?.focus())"
                                class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                            <span :class="$wire.speakerProjectId ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="displayText" class="truncate"></span>
                            <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                <span x-show="$wire.speakerProjectId" @click.stop="choose('')" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openProj ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <div x-show="openProj"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-72 flex flex-col"
                             style="display: none;">
                            <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input x-ref="searchInp" x-model="searchProj" type="text" placeholder="Type to search project, manual, track, timing…"
                                           class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                            </div>
                            <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-56">
                                <button type="button" @click="choose('')"
                                        class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                    — Custom / None (Clear Selection) —
                                </button>
                                <template x-for="p in filteredProjects" :key="p.id">
                                    <button type="button" @click="choose(p.id)"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors text-left"
                                            :class="String($wire.speakerProjectId) === String(p.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                        <div class="truncate mr-2">
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold mr-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300" x-text="p.badge"></span>
                                            <span x-text="p.name" class="font-medium"></span>
                                            <span x-show="p.track" class="text-[11px] text-slate-400 dark:text-slate-500 ml-1" x-text="'· ' + p.track"></span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 flex-shrink-0" x-text="p.duration"></span>
                                    </button>
                                </template>
                                <div x-show="filteredProjects.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                    No matching projects found
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Speech Topic / Title</label>
                    <input wire:model="speakerTopic" type="text" placeholder="e.g. The Power of Vulnerability"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Speech Type / Track</label>
                        <input wire:model="speakerSpeechType"
                               x-ref="speakerSpeechTypeInput"
                               type="text" placeholder="e.g. Competent Communication / Ice Breaker"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Duration</label>
                            <span x-show="isCatalogProject" x-cloak class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold">Project timing</span>
                        </div>
                        <input wire:model="speakerDuration"
                               x-ref="speakerDurationInput"
                               type="text" placeholder="e.g. 5-7 mins"
                               :readonly="isCatalogProject"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-semibold text-primary-600 dark:text-primary-400"
                               :class="isCatalogProject ? 'bg-slate-100/90 dark:bg-slate-800/80 cursor-not-allowed border-dashed select-none' : ''">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Project Name</label>
                    <input wire:model="speakerProject"
                           x-ref="speakerProjectInput"
                           type="text" placeholder="e.g. Writing a Speech with Purpose"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="resetSpeakerForm(); showSpeakerModal = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-md transition-all active:scale-95">
                        <svg wire:loading wire:target="signUpAsSpeaker" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="signUpAsSpeaker">Confirm Volunteer</span>
                        <span wire:loading wire:target="signUpAsSpeaker">Signing up…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Volunteer TTM Modal (Alpine.js) --}}
    {{-- ================================================================ --}}
    <div x-show="showTtmModal"
         x-cloak
         @click.self="showTtmModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Volunteer for Table Topics</h3>
                <button @click="showTtmModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="signUpForTtm" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Preferred Topic / Focus (Optional)</label>
                    <input wire:model="ttmTopic" type="text" placeholder="e.g. Ready for any topic!"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Table Topics is an impromptu 1-to-2 minute speaking challenge. You will receive a prompt from the Table Topics Master during the meeting.
                </p>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showTtmModal = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-md transition-all active:scale-95">
                        <svg wire:loading wire:target="signUpForTtm" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="signUpForTtm">Join Table Topics</span>
                        <span wire:loading wire:target="signUpForTtm">Joining…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Live Ah-Counter & Grammarian Tool Modal (100% Alpine.js 0ms Latency) --}}
    {{-- ================================================================ --}}
    <div x-show="showLiveTools"
         x-cloak
         @click.self="showLiveTools = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-5xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6 max-h-[90vh] flex flex-col">
            
            {{-- Modal Header & Tabs --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Live Facilitator Tools</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80">
                                0ms Real-Time
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Meeting #{{ $meeting->meeting_number }} &bull; Instant local counter. Submit final counts when done.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    {{-- Search attendee in modal --}}
                    <div class="relative w-36 sm:w-44">
                        <input x-model="searchAttendee" type="text" placeholder="Search member…"
                               class="w-full pl-7 pr-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    {{-- Tab Switcher --}}
                    <div class="p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center gap-1">
                        <button @click="activeTab = 'ah_counter'" type="button"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                :class="activeTab === 'ah_counter' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                            Ah-Counter
                        </button>
                        <button @click="activeTab = 'grammarian'" type="button"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                :class="activeTab === 'grammarian' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                            Grammarian
                        </button>
                    </div>

                    {{-- Top Header Quick Submit --}}
                    <button x-show="(activeTab === 'ah_counter' && canManageAhCounter) || (activeTab === 'grammarian' && canManageGrammarian)"
                            @click="saveFinalCounts(activeTab)" :disabled="isSavingCounts" type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg x-show="!isSavingCounts" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="isSavingCounts" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSavingCounts ? 'Saving…' : (activeTab === 'ah_counter' ? 'Submit Ah-Counter' : 'Submit Grammarian')"></span>
                    </button>

                    <button @click="showLiveTools = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 1: Live Ah-Counter --}}
            <div x-show="activeTab === 'ah_counter'" class="overflow-y-auto flex-1 space-y-4 pr-1">
                @if($isMeetingLocked)
                <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 text-xs text-slate-600 dark:text-slate-300 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">🔒 Meeting Finalized</span>
                        <span>This meeting is completed. Official tallies and counts are finalized and locked in read-only mode.</span>
                    </div>
                </div>
                @elseif($canManageAhCounter)
                <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 rounded-2xl p-3.5 text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>💡 <strong>Ah-Counter Mode:</strong> Tap <code class="px-1.5 py-0.5 bg-emerald-200/50 dark:bg-emerald-900/50 rounded font-bold">+</code> or <code class="px-1.5 py-0.5 bg-emerald-200/50 dark:bg-emerald-900/50 rounded font-bold">-</code> to tally filler words. (Assigned: <strong>{{ $assignedAhCounterName }}</strong>)</span>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Click &quot;Submit Ah-Counter&quot; when meeting ends</span>
                </div>
                @else
                <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 text-xs text-slate-600 dark:text-slate-300 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">View-Only Mode</span>
                        <span>Only the assigned Ah-Counter (<strong>{{ $assignedAhCounterName }}</strong>), meeting facilitators, or club officers can modify filler counts.</span>
                    </div>
                </div>
                @endif

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-48">Speaker / Role</th>
                                <th class="p-3 text-center">Ah / Um</th>
                                <th class="p-3 text-center">Er</th>
                                <th class="p-3 text-center">Like</th>
                                <th class="p-3 text-center">So / Well</th>
                                <th class="p-3 text-center">Repeats</th>
                                <th class="p-3 text-center">Other</th>
                                <th class="p-3 text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($meetingParticipants as $member)
                            <tr x-show="matchesSearch('{{ addslashes($member['name']) }}', '{{ addslashes(implode(' ', $member['roles'])) }}')"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $member['name'] }}</div>
                                    <div class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold flex items-center gap-1 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                        <span>{{ implode(', ', $member['roles']) }}</span>
                                    </div>
                                </td>

                                {{-- Ah / Um --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'ah_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'ah_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'ah_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'ah_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Er --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'er_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'er_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'er_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'er_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Like --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'like_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'like_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'like_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'like_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- So / Well --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'so_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'so_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'so_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'so_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Repeats --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'repeats_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'repeats_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'repeats_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'repeats_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Other --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-xl">
                                        <button x-show="canManageAhCounter" @click="decrementAh({{ $member['id'] }}, 'other_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-rose-50 text-rose-600 active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold" :class="getAh({{ $member['id'] }}, 'other_count') > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400'"
                                              x-text="getAh({{ $member['id'] }}, 'other_count')"></span>
                                        <button x-show="canManageAhCounter" @click="incrementAh({{ $member['id'] }}, 'other_count')" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-700 font-bold hover:bg-emerald-50 text-emerald-600 active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Total Fillers --}}
                                <td class="p-3 text-center font-extrabold"
                                    :class="getTotal({{ $member['id'] }}) > 0 ? 'text-rose-600 dark:text-rose-400 text-sm' : 'text-slate-400'"
                                    x-text="getTotal({{ $member['id'] }})">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    <div class="max-w-md mx-auto space-y-2">
                                        <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No role players or speakers registered yet</p>
                                        <p class="text-xs text-slate-400 leading-relaxed">Only members with speaking roles (Role Players, Prepared Speakers, Evaluators, and Table Topics Speakers) appear in the Live Counter tracker.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 2: Grammarian Tracker --}}
            <div x-show="activeTab === 'grammarian'" class="overflow-y-auto flex-1 space-y-4 pr-1">
                @if($isMeetingLocked)
                <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 text-xs text-slate-600 dark:text-slate-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">🔒 Meeting Finalized</span>
                        <span>Word of the Day: <strong>{{ $meeting->word_of_the_day ?: 'Not specified' }}</strong> &bull; This meeting is completed. Records and notes are locked in read-only mode.</span>
                    </div>
                </div>
                @elseif($canManageGrammarian)
                <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 rounded-2xl p-3.5 text-xs text-emerald-800 dark:text-emerald-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse inline-block mr-1"></span>
                        <span class="font-bold">Word of the Day:</span>
                        <span class="font-extrabold text-sm ml-1 text-emerald-950 dark:text-emerald-200">{{ $meeting->word_of_the_day ?: 'Not specified' }}</span>
                        @if($meeting->word_definition)
                            <span class="text-[11px] text-emerald-900/70 dark:text-emerald-300/70 ml-1 italic">&mdash; {{ $meeting->word_definition }}</span>
                        @endif
                        <span class="ml-2 text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold">(Assigned: {{ $assignedGrammarianName }})</span>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Click &quot;Submit Grammarian&quot; when meeting ends</span>
                </div>
                @else
                <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 text-xs text-slate-600 dark:text-slate-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">View-Only Mode</span>
                        <span>Word of the Day: <strong>{{ $meeting->word_of_the_day ?: 'Not specified' }}</strong> &bull; Only the assigned Grammarian (<strong>{{ $assignedGrammarianName }}</strong>), meeting facilitators, or club officers can modify counts and notes.</span>
                    </div>
                </div>
                @endif

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-48">Speaker / Role</th>
                                <th class="p-3 text-center w-36">WOD Usage</th>
                                <th class="p-3">Good Phrasing</th>
                                <th class="p-3">Grammar Improvements</th>
                                <th class="p-3 text-right w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($meetingParticipants as $member)
                            <tr x-show="matchesSearch('{{ addslashes($member['name']) }}', '{{ addslashes(implode(' ', $member['roles'])) }}')"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $member['name'] }}</div>
                                    <div class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold flex items-center gap-1 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                        <span>{{ implode(', ', $member['roles']) }}</span>
                                    </div>
                                </td>

                                {{-- Word of Day Counter (0ms latency) --}}
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 px-2 py-1 rounded-xl">
                                        <button x-show="canManageGrammarian" @click="decrementWod({{ $member['id'] }})" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-800 font-bold text-amber-700 dark:text-amber-300 shadow-xs active:scale-90 transition-transform">-</button>
                                        <span class="w-5 font-bold text-amber-800 dark:text-amber-300"
                                              x-text="getWod({{ $member['id'] }})"></span>
                                        <button x-show="canManageGrammarian" @click="incrementWod({{ $member['id'] }})" type="button"
                                                class="w-5 h-5 rounded-lg bg-white dark:bg-slate-800 font-bold text-amber-700 dark:text-amber-300 shadow-xs active:scale-110 transition-transform">+</button>
                                    </div>
                                </td>

                                {{-- Good Phrases (Dynamic Alpine binding) --}}
                                <td class="p-3 text-slate-600 dark:text-slate-300 text-[11px]">
                                     <span x-text="getGoodPhrases({{ $member['id'] }}) || '—'"></span>
                                </td>

                                {{-- Awkward Phrases (Dynamic Alpine binding) --}}
                                <td class="p-3 text-slate-600 dark:text-slate-300 text-[11px]">
                                     <span x-text="getAwkwardPhrases({{ $member['id'] }}) || '—'"></span>
                                </td>

                                {{-- Edit Notes button (100% Alpine.js 0ms latency) --}}
                                <td class="p-3 text-right">
                                     <button @click="openGrammarNotes({{ $member['id'] }}, '{{ addslashes($member['name']) }}')" type="button"
                                             class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-[11px] font-semibold transition-colors active:scale-95">
                                         <span x-text="canManageGrammarian ? 'Edit Notes' : 'View Notes'"></span>
                                     </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                     <div class="max-w-md mx-auto space-y-2">
                                         <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No role players or speakers registered yet</p>
                                         <p class="text-xs text-slate-400 leading-relaxed">Only members with speaking roles (Role Players, Prepared Speakers, Evaluators, and Table Topics Speakers) appear in the Grammarian tracker.</p>
                                     </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <p class="text-xs text-slate-400">Counts are stored locally in session until submitted.</p>
                <div class="flex items-center gap-2.5">
                    <button @click="showLiveTools = false" type="button"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Close
                    </button>
                    <button x-show="(activeTab === 'ah_counter' && canManageAhCounter) || (activeTab === 'grammarian' && canManageGrammarian)"
                            @click="saveFinalCounts(activeTab)" :disabled="isSavingCounts" type="button"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                        <svg x-show="!isSavingCounts" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="isSavingCounts" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSavingCounts ? 'Submitting…' : (activeTab === 'ah_counter' ? 'Submit Ah-Counter Counts' : 'Submit Grammarian Report')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Edit Grammarian Notes Sub-Modal (Pure Alpine.js 0ms Latency) --}}
    {{-- ================================================================ --}}
    <div x-show="showGrammarModal"
         x-cloak
         @click.self="showGrammarModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-60 overflow-y-auto bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Grammarian Notes</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="grammarModalUserName"></p>
                </div>
                <button @click="showGrammarModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="saveGrammarNotesModal()" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Good / Eloquent Phrases</label>
                    <textarea x-model="grammarModalGoodPhrases" rows="3" placeholder="Quotes, metaphors, strong imagery used by this speaker…"
                              :readonly="!canManageGrammarian"
                              :class="!canManageGrammarian ? 'bg-slate-100 dark:bg-slate-800/60 cursor-not-allowed text-slate-600 dark:text-slate-300' : ''"
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Awkward / Grammatical Corrections</label>
                    <textarea x-model="grammarModalAwkwardPhrases" rows="3" placeholder="Incomplete sentences, misplaced modifiers, mispronunciations…"
                              :readonly="!canManageGrammarian"
                              :class="!canManageGrammarian ? 'bg-slate-100 dark:bg-slate-800/60 cursor-not-allowed text-slate-600 dark:text-slate-300' : ''"
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showGrammarModal = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        <span x-text="canManageGrammarian ? 'Cancel' : 'Close'"></span>
                    </button>
                    <button x-show="canManageGrammarian" type="submit"
                            class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-md transition-all active:scale-95">
                        Done
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Evaluation Feedback Modal (Pure Alpine.js 0ms Latency) --}}
    {{-- ================================================================ --}}
    <div x-show="showEvalNotesModal"
         x-cloak
         @click.self="showEvalNotesModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-60 overflow-y-auto bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
            
            {{-- Modal Header --}}
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Speech Evaluation Feedback</h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                            Speaker: <strong x-text="evalModalSpeakerName"></strong>
                        </span>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-900/40">
                            Evaluator: <strong x-text="evalModalEvaluatorName"></strong>
                        </span>
                    </div>
                </div>
                <button @click="showEvalNotesModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Edit Form vs Read-Only View --}}
            <template x-if="evalModalCanEdit">
                <form @submit.prevent="saveEvalNotes()" class="space-y-4">
                    <div class="p-3 bg-primary-50/70 dark:bg-primary-950/30 rounded-2xl border border-primary-100 dark:border-primary-900/40 text-[11px] sm:text-xs text-primary-800 dark:text-primary-300 leading-relaxed">
                        <p class="font-bold mb-1">💡 Toastmasters Evaluation Framework:</p>
                        <p>Highlight commendations (strengths), recommendations (1–2 specific areas to grow), and concluding encouraging remarks.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Written Evaluation Notes & Feedback
                        </label>
                        <textarea x-model="evalModalNotes" rows="8"
                                  placeholder="Write detailed feedback for the speaker...&#10;• What you excelled at:&#10;• Suggestions for improvement:&#10;• General impressions:"
                                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all font-sans leading-relaxed"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEvalNotesModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                            Cancel
                        </button>
                        <button type="submit" :disabled="evalModalSaving"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95 whitespace-nowrap flex-shrink-0">
                            <svg x-show="evalModalSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="evalModalSaving ? 'Saving Feedback…' : 'Save Evaluation'"></span>
                        </button>
                    </div>
                </form>
            </template>

            <template x-if="!evalModalCanEdit">
                <div class="space-y-4">
                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Evaluator Remarks</p>
                        <div x-show="evalModalNotes.trim() !== ''" class="text-xs sm:text-sm text-slate-800 dark:text-slate-100 whitespace-pre-line leading-relaxed font-sans" x-text="evalModalNotes"></div>
                        <p x-show="evalModalNotes.trim() === ''" class="text-xs text-slate-400 italic">No feedback comments written yet.</p>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEvalNotesModal = false"
                                class="px-5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                            Close
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Standalone Official Timer Sheet Modal (Pure Alpine.js 0ms Latency) --}}
    {{-- ================================================================ --}}
    <div x-show="showTimerSheet"
         x-cloak
         @click.self="showTimerSheet = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-5xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6 max-h-[90vh] flex flex-col">

            {{-- Modal Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Official Timer Sheet</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80">
                                Timer Report
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Meeting #{{ $meeting->meeting_number }} &bull; Record actual time taken (MM:SS) and qualification status.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    {{-- Tab Switcher --}}
                    <div class="p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center gap-1">
                        <button @click="timerTab = 'speakers'" type="button"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                :class="timerTab === 'speakers' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                            Speakers ({{ $meeting->speakers->count() }})
                        </button>
                        <button @click="timerTab = 'evaluators'" type="button"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                :class="timerTab === 'evaluators' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                            Evaluators ({{ $meeting->evaluations->count() }})
                        </button>
                        <button @click="timerTab = 'ttm'" type="button"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                :class="timerTab === 'ttm' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                            Table Topics ({{ $meeting->ttmSpeakers->count() }})
                        </button>
                    </div>

                    {{-- Header Quick Save --}}
                    <button x-show="canManageTimer" @click="saveAllTimerLogs()" :disabled="isSavingTimer" type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg x-show="!isSavingTimer" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="isSavingTimer" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSavingTimer ? 'Saving…' : 'Save Sheet'"></span>
                    </button>

                    <button @click="showTimerSheet = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            @if($isMeetingLocked)
            <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-xs text-slate-600 dark:text-slate-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">🔒 Meeting Finalized</span>
                    <span>This meeting is completed. Official speech timings and qualification records are locked in read-only mode.</span>
                </div>
            </div>
            @elseif($canManageTimer)
            <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl p-3 text-xs text-indigo-900 dark:text-indigo-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse inline-block"></span>
                    <span class="font-bold">Timer Sheet Active</span>
                    <span class="text-indigo-700 dark:text-indigo-400">&bull; Assigned Timer: <strong>{{ $assignedTimerName }}</strong></span>
                </div>
                <span class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400">Click &quot;Set Time&quot; to pick or record speech duration, then click &quot;Save Sheet&quot;.</span>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-xs text-slate-600 dark:text-slate-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">View-Only Mode</span>
                    <span>Only the assigned Timer (<strong>{{ $assignedTimerName }}</strong>), meeting facilitators, or club officers can record speech times.</span>
                </div>
            </div>
            @endif

            {{-- Segment 1: Prepared Speakers Timer Sheet --}}
            <div x-show="timerTab === 'speakers'" class="overflow-y-auto flex-1 space-y-4 pr-1">
                <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl p-3.5 text-xs text-indigo-900 dark:text-indigo-300 flex items-center justify-between">
                    <span>💡 <strong>Prepared Speech Rules:</strong> 30-second grace period under min time and over max time. Click "Set Time" to launch the interactive timepicker.</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-12 text-center">Slot</th>
                                <th class="p-3">Speaker & Topic</th>
                                <th class="p-3 w-28 text-center">Allotted Time</th>
                                <th class="p-3 w-44 text-center">Actual Time</th>
                                <th class="p-3 w-44">Qualification Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($meeting->speakers as $sp)
                            @php
                                $timerKey = 'prepared_speaker_' . $sp->id;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 text-center">
                                    <span class="font-extrabold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-full text-xs">#{{ $sp->slot }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $sp->user?->name ?? 'Speaker' }}</div>
                                    @if($sp->topic) <div class="text-xs text-slate-500 dark:text-slate-400">&ldquo;{{ $sp->topic }}&rdquo;</div> @endif
                                    @if($sp->speech_type) <div class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold mt-0.5">{{ $sp->speech_type }}</div> @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                        ⏱ {{ $sp->formattedTiming() }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                :disabled="!canManageTimer"
                                                @click="if (canManageTimer) openTimePicker('{{ $timerKey }}', '{{ addslashes($sp->user?->name ?? 'Speaker') }}', '{{ $sp->formattedTiming() }}', 'speakers')"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all border shadow-xs"
                                                :class="!canManageTimer 
                                                    ? 'bg-slate-100/70 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 cursor-default' 
                                                    : (getTimer('{{ $timerKey }}', 'time_taken') 
                                                        ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 cursor-pointer hover:shadow-sm' 
                                                        : 'bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer')">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="getTimer('{{ $timerKey }}', 'time_taken') || (canManageTimer ? 'Set Time' : 'No time')"></span>
                                            <svg x-show="canManageTimer" class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                                x-show="canManageTimer && getTimer('{{ $timerKey }}', 'time_taken')"
                                                @click="setTimer('{{ $timerKey }}', 'time_taken', '')"
                                                class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                title="Clear time">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <select :value="getTimer('{{ $timerKey }}', 'status') || 'within_time'"
                                            :disabled="!canManageTimer"
                                            @change="setTimer('{{ $timerKey }}', 'status', $event.target.value)"
                                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                        <option value="within_time">🟢 Within Time</option>
                                        <option value="over_time">🔴 Over Time</option>
                                        <option value="under_time">🟡 Under Time</option>
                                        <option value="disqualified">⚪ Disqualified</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    No prepared speakers scheduled yet for this meeting.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Segment 2: Evaluators Timer Sheet --}}
            <div x-show="timerTab === 'evaluators'" class="overflow-y-auto flex-1 space-y-4 pr-1">
                <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl p-3.5 text-xs text-indigo-900 dark:text-indigo-300 flex items-center justify-between">
                    <span>💡 <strong>Evaluation Timing Rules:</strong> Allotted time is 2–3 minutes (green at 2:00, amber at 2:30, red at 3:00, 30s grace to 3:30). Click "Set Time" to launch the interactive timepicker.</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">Evaluator</th>
                                <th class="p-3">Speaker Evaluated</th>
                                <th class="p-3 w-28 text-center">Allotted Time</th>
                                <th class="p-3 w-44 text-center">Actual Time</th>
                                <th class="p-3 w-44">Qualification Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($meeting->evaluations as $ev)
                            @php
                                $timerKey = 'evaluator_' . $ev->id;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $ev->evaluator?->name ?? 'Evaluator' }}</div>
                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200/60 dark:border-emerald-900/40">Evaluator</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $ev->speaker?->user?->name ?? 'Speaker' }}</div>
                                    @if($ev->speaker?->topic) <div class="text-[11px] text-slate-400">&ldquo;{{ $ev->speaker->topic }}&rdquo;</div> @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                        ⏱ 2-3 mins
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                :disabled="!canManageTimer"
                                                @click="if (canManageTimer) openTimePicker('{{ $timerKey }}', '{{ addslashes($ev->evaluator?->name ?? 'Evaluator') }}', '2-3 mins', 'evaluators')"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all border shadow-xs"
                                                :class="!canManageTimer 
                                                    ? 'bg-slate-100/70 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 cursor-default' 
                                                    : (getTimer('{{ $timerKey }}', 'time_taken') 
                                                        ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 cursor-pointer hover:shadow-sm' 
                                                        : 'bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer')">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="getTimer('{{ $timerKey }}', 'time_taken') || (canManageTimer ? 'Set Time' : 'No time')"></span>
                                            <svg x-show="canManageTimer" class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                                x-show="canManageTimer && getTimer('{{ $timerKey }}', 'time_taken')"
                                                @click="setTimer('{{ $timerKey }}', 'time_taken', '')"
                                                class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                title="Clear time">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <select :value="getTimer('{{ $timerKey }}', 'status') || 'within_time'"
                                            :disabled="!canManageTimer"
                                            @change="setTimer('{{ $timerKey }}', 'status', $event.target.value)"
                                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                        <option value="within_time">🟢 Within Time</option>
                                        <option value="over_time">🔴 Over Time</option>
                                        <option value="under_time">🟡 Under Time</option>
                                        <option value="disqualified">⚪ Disqualified</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    No speech evaluations scheduled yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Segment 3: Table Topics (TTM) Timer Sheet --}}
            <div x-show="timerTab === 'ttm'" class="overflow-y-auto flex-1 space-y-4 pr-1">
                <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl p-3.5 text-xs text-indigo-900 dark:text-indigo-300 flex items-center justify-between">
                    <span>💡 <strong>Table Topics Timing Rules:</strong> 1–2 minutes per speaker (green at 1:00, amber at 1:30, red at 2:00, 30s grace to 2:30). Click "Set Time" to launch the interactive timepicker.</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3 w-12 text-center">Slot</th>
                                <th class="p-3">Speaker & Topic</th>
                                <th class="p-3 w-28 text-center">Allotted Time</th>
                                <th class="p-3 w-44 text-center">Actual Time</th>
                                <th class="p-3 w-44">Qualification Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($meeting->ttmSpeakers as $ttm)
                            @php
                                $timerKey = 'ttm_speaker_' . $ttm->id;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 text-center">
                                    <span class="font-extrabold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-full text-xs">#{{ $ttm->slot }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $ttm->user?->name ?? 'Participant' }}</div>
                                    @if($ttm->topic) <div class="text-xs text-slate-500 dark:text-slate-400">&ldquo;{{ $ttm->topic }}&rdquo;</div> @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                        ⏱ 1-2 mins
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                :disabled="!canManageTimer"
                                                @click="if (canManageTimer) openTimePicker('{{ $timerKey }}', '{{ addslashes($ttm->user?->name ?? 'Participant') }}', '{{ $ttm->formattedTiming() }}', 'ttm')"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all border shadow-xs"
                                                :class="!canManageTimer 
                                                    ? 'bg-slate-100/70 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 cursor-default' 
                                                    : (getTimer('{{ $timerKey }}', 'time_taken') 
                                                        ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 cursor-pointer hover:shadow-sm' 
                                                        : 'bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer')">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="getTimer('{{ $timerKey }}', 'time_taken') || (canManageTimer ? 'Set Time' : 'No time')"></span>
                                            <svg x-show="canManageTimer" class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                                x-show="canManageTimer && getTimer('{{ $timerKey }}', 'time_taken')"
                                                @click="setTimer('{{ $timerKey }}', 'time_taken', '')"
                                                class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                title="Clear time">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <select :value="getTimer('{{ $timerKey }}', 'status') || 'within_time'"
                                            :disabled="!canManageTimer"
                                            @change="setTimer('{{ $timerKey }}', 'status', $event.target.value)"
                                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                        <option value="within_time">🟢 Within Time</option>
                                        <option value="over_time">🔴 Over Time</option>
                                        <option value="under_time">🟡 Under Time</option>
                                        <option value="disqualified">⚪ Disqualified</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    No Table Topics speakers registered yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <p class="text-xs text-slate-400">Timer records are stored in the official meeting report and PDF minutes.</p>
                <div class="flex items-center gap-2.5">
                    <button @click="showTimerSheet = false" type="button"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Close
                    </button>
                    <button x-show="canManageTimer" @click="saveAllTimerLogs()" :disabled="isSavingTimer" type="button"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                        <svg x-show="!isSavingTimer" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="isSavingTimer" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSavingTimer ? 'Saving Timer Sheet…' : 'Save Timer Sheet'"></span>
                    </button>
                </div>
            </div>

            {{-- Unified Interactive Custom Timepicker Popover / Modal (z-60) --}}
            <div x-show="activeTimePicker !== null"
                 x-cloak
                 @click.self="closeTimePicker()"
                 @keydown.escape.window="closeTimePicker()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-60 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
                
                <div @click.stop
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeTimePicker?.name || 'Select Time'"></h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[10px] font-semibold text-slate-400">Allotted:</span>
                                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400" x-text="activeTimePicker?.allotted"></span>
                            </div>
                        </div>
                        <button @click="closeTimePicker()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Digital Time Display --}}
                    <div class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/40 rounded-2xl py-3.5 px-4 text-center">
                        <div class="text-3xl font-extrabold font-mono tracking-wider text-indigo-700 dark:text-indigo-300 flex items-center justify-center gap-2">
                            <span x-text="pickerMin"></span>
                            <span class="text-indigo-400 animate-pulse">:</span>
                            <span x-text="pickerSec"></span>
                        </div>
                        <div class="flex items-center justify-center gap-12 text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">
                            <span>Minutes</span>
                            <span>Seconds</span>
                        </div>
                    </div>

                    {{-- Scrollable Columns for Minutes & Seconds --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Minutes Column --}}
                        <div class="space-y-1.5">
                            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center">Minutes</div>
                            <div class="h-44 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-800 p-1.5 space-y-1 bg-slate-50/50 dark:bg-slate-800/30">
                                <template x-for="m in 31" :key="m - 1">
                                    <button type="button"
                                            @click="selectPickerMin(String(m - 1).padStart(2, '0'))"
                                            class="w-full py-1.5 px-2.5 rounded-xl text-xs font-mono font-bold transition-all flex items-center justify-between"
                                            :class="pickerMin === String(m - 1).padStart(2, '0')
                                                ? 'bg-indigo-600 text-white shadow-xs'
                                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700/60'">
                                        <span x-text="String(m - 1).padStart(2, '0') + ' min'"></span>
                                        <span x-show="pickerMin === String(m - 1).padStart(2, '0')">✓</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Seconds Column --}}
                        <div class="space-y-1.5">
                            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-center">Seconds</div>
                            <div class="h-44 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-800 p-1.5 space-y-1 bg-slate-50/50 dark:bg-slate-800/30">
                                <template x-for="s in 60" :key="s - 1">
                                    <button type="button"
                                            @click="selectPickerSec(String(s - 1).padStart(2, '0'))"
                                            class="w-full py-1.5 px-2.5 rounded-xl text-xs font-mono font-bold transition-all flex items-center justify-between"
                                            :class="pickerSec === String(s - 1).padStart(2, '0')
                                                ? 'bg-indigo-600 text-white shadow-xs'
                                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700/60'">
                                        <span x-text="String(s - 1).padStart(2, '0') + ' sec'"></span>
                                        <span x-show="pickerSec === String(s - 1).padStart(2, '0')">✓</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Preset Chips --}}
                    <div class="space-y-1.5 pt-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Quick Presets</div>
                        <div>
                            <template x-if="activeTimePicker?.type === 'speakers'">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="preset in ['04:30', '05:00', '06:00', '07:00', '07:30']" :key="preset">
                                        <button type="button"
                                                @click="selectPickerPreset(preset)"
                                                class="px-2.5 py-1 text-xs font-mono font-semibold rounded-lg border transition-all"
                                                :class="(pickerMin + ':' + pickerSec) === preset
                                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200'">
                                            <span x-text="preset"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="activeTimePicker?.type === 'evaluators'">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="preset in ['01:30', '02:00', '02:30', '03:00', '03:30']" :key="preset">
                                        <button type="button"
                                                @click="selectPickerPreset(preset)"
                                                class="px-2.5 py-1 text-xs font-mono font-semibold rounded-lg border transition-all"
                                                :class="(pickerMin + ':' + pickerSec) === preset
                                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200'">
                                            <span x-text="preset"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="activeTimePicker?.type === 'ttm'">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="preset in ['00:45', '01:00', '01:30', '02:00', '02:30']" :key="preset">
                                        <button type="button"
                                                @click="selectPickerPreset(preset)"
                                                class="px-2.5 py-1 text-xs font-mono font-semibold rounded-lg border transition-all"
                                                :class="(pickerMin + ':' + pickerSec) === preset
                                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200'">
                                            <span x-text="preset"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button @click="clearPickerTime()" type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors">
                            Clear Time
                        </button>
                        <button @click="applyPickerTime(); closeTimePicker()" type="button"
                                class="px-5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow transition-colors">
                            Done
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Listening Master Report Modal --}}
    {{-- ================================================================ --}}
    <div x-show="showListeningMasterModal"
         x-cloak
         @click.self="showListeningMasterModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 flex flex-col max-h-[90vh]">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Listening Master Report</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Assigned Listening Master: <strong class="text-slate-700 dark:text-slate-200">{{ $assignedListeningMasterName }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($canManageListeningMaster)
                    <button wire:click="saveListeningMasterReport" wire:loading.attr="disabled" type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-600/20 transition-all">
                        <svg wire:loading.remove wire:target="saveListeningMasterReport" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg wire:loading wire:target="saveListeningMasterReport" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveListeningMasterReport">Save Report</span>
                        <span wire:loading wire:target="saveListeningMasterReport">Saving…</span>
                    </button>
                    @endif

                    <button @click="showListeningMasterModal = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Role Context Banner --}}
            @if($isMeetingLocked)
            <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2 flex-shrink-0">
                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">🔒 Meeting Finalized</span>
                <span>This meeting is completed. The official Listening Master report is locked in read-only mode.</span>
            </div>
            @elseif($canManageListeningMaster)
            <div class="bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-900/40 rounded-2xl p-3 text-xs text-purple-900 dark:text-purple-300 flex items-center justify-between gap-2 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse inline-block"></span>
                    <span class="font-bold">Listening Master Active</span>
                    <span class="text-purple-700 dark:text-purple-400">&bull; Questions and observations will be included in the meeting report and PDF.</span>
                </div>
                <span class="text-[11px] font-medium text-purple-600 dark:text-purple-400">Click &quot;Save Report&quot; when finished.</span>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2 flex-shrink-0">
                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">View-Only Mode</span>
                <span>Only the assigned Listening Master (<strong>{{ $assignedListeningMasterName }}</strong>), meeting facilitators, or club officers can edit this report.</span>
            </div>
            @endif

            {{-- Body Content --}}
            <div class="overflow-y-auto flex-1 pr-1 space-y-4">
                @if($canManageListeningMaster && !$isMeetingLocked)
                <div>
                    <x-rich-text-editor
                        wire:model="listeningMasterReport"
                        placeholder="Type listening quiz questions, observations, and attendee responses here…"
                    />
                </div>
                @else
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/50 min-h-[220px]">
                    @if($meeting->listening_master_report)
                        <div class="rich-text-content text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                            {!! $meeting->listening_master_report !!}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 opacity-40 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                            <p class="text-sm font-semibold">No Listening Master report recorded yet.</p>
                            <p class="text-xs text-slate-400 mt-1">The report will appear here once recorded by the role player.</p>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
                <span class="text-[11px] text-slate-400">💡 Listening Master tests members' active listening skills throughout the meeting.</span>
                <button type="button" @click="showListeningMasterModal = false"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Minutes of Meeting (MoM) Modal --}}
    {{-- ================================================================ --}}
    <div x-show="showMinutesModal"
         x-cloak
         @click.self="showMinutesModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 flex flex-col max-h-[90vh]">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Minutes of Meeting (MoM)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Record of official proceedings, executive decisions, guest introductions, and awards.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($canManageMinutes)
                    <button wire:click="saveMinutesOfMeeting" wire:loading.attr="disabled" type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 active:scale-95 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md shadow-primary-600/20 transition-all">
                        <svg wire:loading.remove wire:target="saveMinutesOfMeeting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg wire:loading wire:target="saveMinutesOfMeeting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveMinutesOfMeeting">Save Minutes</span>
                        <span wire:loading wire:target="saveMinutesOfMeeting">Saving…</span>
                    </button>
                    @endif

                    <button @click="showMinutesModal = false" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Role Context Banner --}}
            @if($canManageMinutes)
            <div class="bg-primary-50/70 dark:bg-primary-950/30 border border-primary-200/60 dark:border-primary-900/40 rounded-2xl p-3 text-xs text-primary-900 dark:text-primary-300 flex items-center justify-between gap-2 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse inline-block"></span>
                    <span class="font-bold">Minutes Editor</span>
                    <span class="text-primary-700 dark:text-primary-400">&bull; Changes will be reflected on the meeting view page, meeting report, and PDF.</span>
                </div>
                <span class="text-[11px] font-medium text-primary-600 dark:text-primary-400">Click &quot;Save Minutes&quot; when finished.</span>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2 flex-shrink-0">
                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">View-Only Mode</span>
                <span>Only club officers, secretaries, or meeting administrators can edit these minutes.</span>
            </div>
            @endif

            {{-- Body Content --}}
            <div class="overflow-y-auto flex-1 pr-1 space-y-4">
                @if($canManageMinutes)
                <div>
                    <x-rich-text-editor
                        wire:model="minutesOfMeeting"
                        placeholder="Record key meeting proceedings, call to order, officer reports, motions approved, awards, guest remarks…"
                    />
                </div>
                @else
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/50 min-h-[220px]">
                    @if($meeting->minutes_of_meeting)
                        <div class="rich-text-content text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                            {!! $meeting->minutes_of_meeting !!}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 opacity-40 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm font-semibold">No Minutes of Meeting recorded yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Official meeting minutes will appear here once saved by the Secretary or meeting officer.</p>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
                <span class="text-[11px] text-slate-400">📄 Minutes are published in the official meeting report and PDF minutes.</span>
                <button type="button" @click="showMinutesModal = false"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
