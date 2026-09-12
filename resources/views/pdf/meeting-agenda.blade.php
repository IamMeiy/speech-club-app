@php
    $themePalettes = [
        'indigo' => [
            '50' => '#eef2ff', '100' => '#e0e7ff', '200' => '#c7d2fe', '300' => '#a5b4fc',
            '400' => '#818cf8', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca',
            '800' => '#3730a3', '900' => '#312e81',
        ],
        'emerald' => [
            '50' => '#ecfdf5', '100' => '#d1fae5', '200' => '#a7f3d0', '300' => '#6ee7b7',
            '400' => '#34d399', '500' => '#10b981', '600' => '#059669', '700' => '#047857',
            '800' => '#065f46', '900' => '#064e3b',
        ],
        'blue' => [
            '50' => '#eff6ff', '100' => '#dbeafe', '200' => '#bfdbfe', '300' => '#93c5fd',
            '400' => '#60a5fa', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8',
            '800' => '#1e40af', '900' => '#1e3a8a',
        ],
        'purple' => [
            '50' => '#faf5ff', '100' => '#f3e8ff', '200' => '#e9d5ff', '300' => '#d8b4fe',
            '400' => '#c084fc', '500' => '#a855f7', '600' => '#9333ea', '700' => '#7e22ce',
            '800' => '#6b21a8', '900' => '#581c87',
        ],
        'rose' => [
            '50' => '#fff1f2', '100' => '#ffe4e6', '200' => '#fecdd3', '300' => '#fda4af',
            '400' => '#fb7185', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c',
            '800' => '#9f1239', '900' => '#881337',
        ],
        'amber' => [
            '50' => '#fffbeb', '100' => '#fef3c7', '200' => '#fde68a', '300' => '#fcd34d',
            '400' => '#fbbf24', '500' => '#f59e0b', '600' => '#d97706', '700' => '#b45309',
            '800' => '#92400e', '900' => '#78350f',
        ],
        'cyan' => [
            '50' => '#ecfeff', '100' => '#cffafe', '200' => '#a5f3fc', '300' => '#67e8f9',
            '400' => '#22d3ee', '500' => '#06b6d4', '600' => '#0891b2', '700' => '#0e7490',
            '800' => '#155e75', '900' => '#164e63',
        ],
    ];

    $th = $themePalettes[$theme ?? 'indigo'] ?? $themePalettes['indigo'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Meeting Agenda #{{ $meeting->meeting_number }} — {{ $meeting->club->name }}</title>
    <style>
        @page {
            margin: 26px 28px 30px 28px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10.5px;
            line-height: 1.4;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .top-bar {
            height: 4px;
            background-color: {{ $th['600'] }};
            border-radius: 2px;
            margin-bottom: 14px;
        }

        .club-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0 0 2px 0;
        }

        .club-subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .meeting-badge-box {
            background-color: {{ $th['50'] }};
            border: 1.5px solid {{ $th['300'] }};
            border-radius: 8px;
            padding: 7px 14px;
            text-align: right;
            display: inline-block;
        }

        .meeting-badge-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ $th['700'] }};
            margin: 0;
        }

        .meeting-badge-date {
            font-size: 9.5px;
            color: #475569;
            margin: 2px 0 0 0;
            font-weight: 500;
        }

        .info-pill-bar {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .info-pill-cell {
            padding: 6px 10px;
            font-size: 9.5px;
            vertical-align: middle;
            border-right: 1px solid #e2e8f0;
        }

        .info-pill-cell:last-child {
            border-right: none;
        }

        .info-pill-label {
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            display: block;
        }

        .info-pill-val {
            font-size: 10px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Theme Card */
        .theme-box {
            background: #ffffff;
            border-left: 3.5px solid {{ $th['600'] }};
            background-color: {{ $th['50'] }};
            border-top: 1px solid {{ $th['100'] }};
            border-right: 1px solid {{ $th['100'] }};
            border-bottom: 1px solid {{ $th['100'] }};
            border-radius: 0 6px 6px 0;
            padding: 8px 12px;
            margin-bottom: 12px;
        }

        .theme-label {
            font-size: 8px;
            font-weight: bold;
            color: {{ $th['700'] }};
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .theme-content {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Word of the Day Box */
        .wotd-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 3.5px solid #f59e0b;
            border-radius: 0 6px 6px 0;
            padding: 7px 12px;
            margin-bottom: 12px;
        }

        .wotd-header {
            font-size: 8.5px;
            font-weight: bold;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .wotd-word {
            font-size: 13px;
            font-weight: bold;
            color: #78350f;
            display: inline;
        }

        .wotd-pos {
            font-size: 9.5px;
            font-style: italic;
            color: #92400e;
            display: inline;
            margin-left: 4px;
        }

        .wotd-def {
            font-size: 9.5px;
            color: #451a03;
            margin-top: 2px;
        }

        .wotd-eg {
            font-size: 9px;
            color: #78350f;
            font-style: italic;
            margin-top: 2px;
        }

        /* Section Headings */
        .section-header {
            background-color: {{ $th['600'] }};
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
            margin-top: 10px;
        }

        /* Tables */
        .agenda-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .agenda-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            text-align: left;
            border-bottom: 1.5px solid #cbd5e1;
        }

        .agenda-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 9.5px;
        }

        .agenda-table tr:last-child td {
            border-bottom: none;
        }

        .role-bearer-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }

        .roles-grid-table {
            width: 100%;
            border-collapse: collapse;
        }

        .roles-grid-table td {
            padding: 3px 6px;
            font-size: 9.5px;
            vertical-align: middle;
            width: 33.33%;
        }

        .role-name {
            font-weight: bold;
            color: #475569;
            font-size: 8.5px;
            text-transform: uppercase;
        }

        .role-person {
            font-weight: 600;
            color: #0f172a;
            font-size: 9.5px;
        }

        .vacant-role {
            color: #94a3b8;
            font-style: italic;
            font-weight: normal;
        }

        .time-col {
            width: 75px;
            font-weight: bold;
            color: {{ $th['700'] }};
            font-size: 9px;
            white-space: nowrap;
        }

        .dur-col {
            width: 50px;
            text-align: right;
            color: #64748b;
            font-size: 9px;
        }

        .speaker-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
        }

        .speaker-details {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .eval-badge {
            background-color: {{ $th['50'] }};
            color: {{ $th['700'] }};
            border: 1px solid {{ $th['200'] }};
            border-radius: 4px;
            padding: 1px 4px;
            font-size: 8.5px;
            font-weight: 600;
            display: inline-block;
        }

        /* Footer */
        .page-footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }

        .mission-box {
            font-style: italic;
            color: #64748b;
            font-size: 8.5px;
            text-align: center;
            margin-top: 10px;
            padding: 6px 12px;
            background-color: #f8fafc;
            border-radius: 4px;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>
<body>

    <div class="top-bar"></div>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="club-title">{{ $meeting->club->name }}</h1>
                <p class="club-subtitle">Club #{{ $meeting->club->code }} &bull; Toastmasters International &bull; Official Agenda</p>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="meeting-badge-box">
                    <div class="meeting-badge-title">Meeting #{{ $meeting->meeting_number }}</div>
                    <div class="meeting-badge-date">{{ $meeting->meeting_date->format('l, F j, Y') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Info Pill Bar --}}
    <table class="info-pill-bar">
        <tr>
            <td class="info-pill-cell" style="width: 25%;">
                <span class="info-pill-label">Time</span>
                <span class="info-pill-val">
                    @if($meeting->start_time)
                        {{ $meeting->formattedTime() }}
                    @else
                        {{ $meeting->club->meeting_time ?? 'TBD' }}
                    @endif
                </span>
            </td>
            <td class="info-pill-cell" style="width: 35%;">
                <span class="info-pill-label">Venue / Location</span>
                <span class="info-pill-val">{{ $meeting->venue ?: ($meeting->club->location ?: 'Club Meeting Room') }}</span>
            </td>
            <td class="info-pill-cell" style="width: 20%;">
                <span class="info-pill-label">Status</span>
                <span class="info-pill-val" style="text-transform: capitalize;">{{ $meeting->status }}</span>
            </td>
            <td class="info-pill-cell" style="width: 20%;">
                <span class="info-pill-label">Toastmaster</span>
                @php
                    $tmodRole = $meeting->roles->first(fn($r) => in_array(strtolower($r->roleType?->slug ?? ''), ['tmod', 'toastmaster', 'toastmaster-of-the-day']));
                @endphp
                <span class="info-pill-val">{{ $tmodRole?->user?->name ?? 'TBD' }}</span>
            </td>
        </tr>
    </table>

    {{-- Theme Banner --}}
    @if($meeting->theme)
    <div class="theme-box">
        <div class="theme-label">Meeting Theme</div>
        <div class="theme-content">&ldquo;{{ $meeting->theme }}&rdquo;</div>
    </div>
    @endif

    {{-- Word of the Day --}}
    @if($meeting->hasWordOfTheDay())
    <div class="wotd-box">
        <div class="wotd-header">&#10022; Word of the Day & Vocabulary Challenge</div>
        <div style="margin-top: 2px;">
            <span class="wotd-word">{{ $meeting->word_of_the_day }}</span>
            @if($meeting->word_part_of_speech)
                <span class="wotd-pos">({{ $meeting->word_part_of_speech }})</span>
            @endif
        </div>
        @if($meeting->word_definition)
            <div class="wotd-def"><strong>Definition:</strong> {{ $meeting->word_definition }}</div>
        @endif
        @if($meeting->word_example_sentence)
            <div class="wotd-eg"><strong>Example:</strong> &ldquo;{{ $meeting->word_example_sentence }}&rdquo;</div>
        @endif
    </div>
    @endif

    {{-- Meeting Facilitators & Leadership Table --}}
    <div class="section-header">Meeting Leadership & Facilitator Team</div>
    <div class="role-bearer-card">
        <table class="roles-grid-table">
            @php
                $roleChunks = $meeting->roles->chunk(3);
            @endphp
            @forelse($roleChunks as $row)
            <tr>
                @foreach($row as $role)
                <td>
                    <span class="role-name">{{ $role->roleType->name ?? 'Role' }}:</span>
                    <span class="role-person">{{ $role->user->name ?? 'Vacant' }}</span>
                </td>
                @endforeach
                @for($i = $row->count(); $i < 3; $i++)
                <td></td>
                @endfor
            </tr>
            @empty
            <tr>
                <td colspan="3" class="vacant-role" style="text-align: center; padding: 6px;">
                    Role signups are currently open. Volunteer to take up a role!
                </td>
            </tr>
            @endforelse
        </table>
    </div>

    {{-- Order of Program / Schedule --}}
    <div class="section-header">Order of Meeting & Program Schedule</div>
    <table class="agenda-table">
        <thead>
            <tr>
                <th style="width: 14%;">Segment</th>
                <th style="width: 48%;">Activity & Program Details</th>
                <th style="width: 26%;">Facilitator / Role Bearer</th>
                <th style="width: 12%; text-align: right;">Time Allotted</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Call to Order</strong></td>
                <td>Welcome, Club Mission & Introduction of President / TMOD</td>
                <td>Sergeant-at-Arms / Presiding Officer</td>
                <td style="text-align: right;">3-5 mins</td>
            </tr>
            <tr>
                <td><strong>President's Address</strong></td>
                <td>Opening Remarks, Welcome Guests, Presidential Address</td>
                <td>Club President</td>
                <td style="text-align: right;">3 mins</td>
            </tr>
            <tr>
                <td><strong>TMOD Introduction</strong></td>
                <td>Introduction of Meeting Theme, Explain Flow of Program</td>
                <td>{{ $tmodRole?->user?->name ?? 'Toastmaster of the Day' }}</td>
                <td style="text-align: right;">3 mins</td>
            </tr>
            <tr>
                <td><strong>Role Introductions</strong></td>
                <td>Introduction of Grammarian, Ah-Counter, and Timer teams</td>
                <td>General Evaluator & Team</td>
                <td style="text-align: right;">4 mins</td>
            </tr>

            {{-- Prepared Speeches --}}
            @if($meeting->speakers->isNotEmpty())
            <tr style="background-color: {{ $th['50'] }};">
                <td colspan="4" style="font-weight: bold; color: {{ $th['700'] }}; font-size: 9px; padding: 4px 8px;">
                    PART I: PREPARED SPEECH SESSION
                </td>
            </tr>
            @foreach($meeting->speakers as $speaker)
            <tr>
                <td><strong>Speaker #{{ $speaker->slot }}</strong></td>
                <td>
                    <div class="speaker-title">{{ $speaker->topic ?: 'Title to be announced' }}</div>
                    <div class="speaker-details">
                        @if($speaker->speech_type) <span style="font-weight: 600;">{{ $speaker->speech_type }}</span> @endif
                        @if($speaker->project) &bull; <span>{{ $speaker->project }}</span> @endif
                    </div>
                </td>
                <td>
                    <div style="font-weight: 600; color: #0f172a;">{{ $speaker->user?->name ?? 'TBD' }}</div>
                    @if($speaker->evaluation?->evaluator)
                        <div style="margin-top: 1px;">
                            <span class="eval-badge">Evaluator: {{ $speaker->evaluation->evaluator->name }}</span>
                        </div>
                    @endif
                </td>
                <td style="text-align: right; color: #475569; font-weight: 500;">
                    {{ $speaker->duration ?: '5-7 mins' }}
                </td>
            </tr>
            @endforeach
            @endif

            {{-- Table Topics --}}
            <tr style="background-color: #f8fafc;">
                <td colspan="4" style="font-weight: bold; color: #334155; font-size: 9px; padding: 4px 8px;">
                    PART II: TABLE TOPICS (IMPROMPTU SPEAKING)
                </td>
            </tr>
            <tr>
                <td><strong>Table Topics</strong></td>
                <td>
                    <div>Impromptu Speaking Session (1-2 minutes per speaker)</div>
                    @if($meeting->ttmSpeakers->isNotEmpty())
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                        Participants: {{ $meeting->ttmSpeakers->map(fn($t) => $t->user?->name)->filter()->join(', ') }}
                    </div>
                    @endif
                </td>
                @php
                    $ttmRole = $meeting->roles->first(fn($r) => in_array(strtolower($r->roleType?->slug ?? ''), ['ttm', 'table-topics-master']));
                @endphp
                <td><strong>{{ $ttmRole?->user?->name ?? 'Table Topics Master' }}</strong></td>
                <td style="text-align: right;">15-20 mins</td>
            </tr>

            {{-- Evaluations & Reports --}}
            <tr style="background-color: #f8fafc;">
                <td colspan="4" style="font-weight: bold; color: #334155; font-size: 9px; padding: 4px 8px;">
                    PART III: EVALUATION & OFFICIAL REPORTS
                </td>
            </tr>
            <tr>
                <td><strong>Speech Evaluations</strong></td>
                <td>Constructive feedback for prepared speech presenters (2-3 mins each)</td>
                <td>Assigned Speech Evaluators</td>
                <td style="text-align: right;">8-12 mins</td>
            </tr>
            <tr>
                <td><strong>Facilitator Reports</strong></td>
                <td>Timer Report, Ah-Counter Report & Grammarian Vocabulary Report</td>
                <td>Timer, Ah-Counter, Grammarian</td>
                <td style="text-align: right;">5 mins</td>
            </tr>
            <tr>
                <td><strong>General Evaluation</strong></td>
                <td>Comprehensive evaluation of the entire meeting and team</td>
                @php
                    $geRole = $meeting->roles->first(fn($r) => in_array(strtolower($r->roleType?->slug ?? ''), ['ge', 'general-evaluator']));
                @endphp
                <td><strong>{{ $geRole?->user?->name ?? 'General Evaluator' }}</strong></td>
                <td style="text-align: right;">5 mins</td>
            </tr>
            <tr>
                <td><strong>Closing & Awards</strong></td>
                <td>Guest impressions, presentation of awards, closing remarks</td>
                <td>TMOD / Club President</td>
                <td style="text-align: right;">5 mins</td>
            </tr>
        </tbody>
    </table>

    {{-- Meeting Notes if available --}}
    @if($meeting->notes)
    <div class="section-header">Club Announcements & Meeting Notes</div>
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 10px; font-size: 9px; line-height: 1.4; color: #334155; margin-bottom: 10px;">
        {!! $meeting->notes !!}
    </div>
    @endif

    {{-- Club Mission / Toastmasters values --}}
    <div class="mission-box">
        <strong>Club Mission:</strong> We provide a supportive and positive learning experience in which members are empowered to develop communication and leadership skills, resulting in greater self-confidence and personal growth.
    </div>

    <div class="page-footer">
        Generated on {{ now()->format('M d, Y g:i A') }} &bull; {{ $meeting->club->name }} &bull; Page 1 of 1
    </div>

</body>
</html>
