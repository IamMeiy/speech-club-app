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
    <title>Meeting #{{ $meeting->meeting_number }} Report — {{ $meeting->club->name }}</title>
    <style>
        @page {
            margin: 28px 30px 34px 30px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ------------------------------------------------------------- */
        /* Header & Banner */
        /* ------------------------------------------------------------- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .header-left {
            vertical-align: middle;
        }

        .top-accent-bar {
            height: 4px;
            background: {{ $th['600'] }};
            border-radius: 2px;
            margin-bottom: 14px;
        }

        .brand-badge {
            display: inline-block;
            background: {{ $th['50'] }};
            color: {{ $th['700'] }};
            border: 1px solid {{ $th['200'] }};
            font-weight: bold;
            font-size: 8.5px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
        }

        .doc-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .doc-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
            font-weight: 500;
        }

        .header-right {
            text-align: right;
            vertical-align: middle;
        }

        .meeting-num-pill {
            display: inline-block;
            background: {{ $th['600'] }};
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 8px;
            letter-spacing: -0.3px;
        }

        /* ------------------------------------------------------------- */
        /* Highlight Summary Card */
        /* ------------------------------------------------------------- */
        .info-card {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .info-card td {
            padding: 10px 14px;
            vertical-align: top;
            border-right: 1px solid #e2e8f0;
        }

        .info-card td:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed { background: #dcfce7; color: #15803d; }
        .status-scheduled { background: #dbeafe; color: #1d4ed8; }
        .status-draft { background: #f1f5f9; color: #475569; }
        .status-cancelled { background: #ffe4e6; color: #be123c; }

        .att-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .att-present { background: #dcfce7; color: #15803d; }
        .att-late    { background: #fef3c7; color: #b45309; }
        .att-excused { background: #f1f5f9; color: #475569; }
        .att-absent  { background: #ffe4e6; color: #be123c; }

        .timer-badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }
        .timer-within { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .timer-over   { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .timer-under  { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .timer-dq     { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .att-icon {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: normal;
            font-size: 9.5px;
            margin-right: 3px;
        }

        /* ------------------------------------------------------------- */
        /* Theme Box */
        /* ------------------------------------------------------------- */
        .theme-box {
            background: {{ $th['50'] }};
            border-left: 4px solid {{ $th['500'] }};
            border-top: 1px solid {{ $th['100'] }};
            border-right: 1px solid {{ $th['100'] }};
            border-bottom: 1px solid {{ $th['100'] }};
            padding: 9px 14px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 16px;
        }

        .theme-box-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: {{ $th['600'] }};
            font-weight: 700;
        }

        .theme-box-title {
            font-size: 13px;
            font-weight: 700;
            color: {{ $th['900'] }};
            margin-top: 2px;
            font-style: italic;
        }

        /* ------------------------------------------------------------- */
        /* Attendance Stats Strip */
        /* ------------------------------------------------------------- */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 16px;
        }

        .stat-card {
            text-align: center;
            padding: 10px 4px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            width: 20%;
        }

        .stat-card.present { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .stat-card.absent { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
        .stat-card.late { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .stat-card.excused { background: #f8fafc; border-color: #e2e8f0; color: #475569; }
        .stat-card.rate { background: {{ $th['50'] }}; border-color: {{ $th['200'] }}; color: {{ $th['700'] }}; }

        .stat-num {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.1;
        }

        .stat-text {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        /* ------------------------------------------------------------- */
        /* Section Containers & Tables */
        /* ------------------------------------------------------------- */
        .section-container {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .section-heading {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #1e293b;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $th['200'] }};
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            padding: 7px 10px;
            text-align: left;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }

        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background: #fbfcfe;
        }

        .two-column-table {
            width: 100%;
            border-collapse: collapse;
        }

        .two-column-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .two-column-table > tbody > tr > td:first-child {
            padding-right: 10px;
        }

        .two-column-table > tbody > tr > td:last-child {
            padding-left: 10px;
        }

        .slot-badge {
            display: inline-block;
            background: {{ $th['50'] }};
            color: {{ $th['700'] }};
            border: 1px solid {{ $th['200'] }};
            font-weight: 800;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .role-title {
            font-weight: 700;
            color: #475569;
        }

        .user-name {
            font-weight: 700;
            color: #0f172a;
        }

        .notes-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 10.5px;
            color: #334155;
            line-height: 1.55;
        }

        .notes-box p { margin: 0 0 6px 0; }
        .notes-box p:last-child { margin-bottom: 0; }
        .notes-box ul, .notes-box ol { margin: 4px 0 6px 18px; padding: 0; }
        .notes-box li { margin-bottom: 2px; }
        .notes-box strong, .notes-box b { font-weight: 700; color: #0f172a; }
        .notes-box h2, .notes-box h3, .notes-box h4 { font-size: 11px; font-weight: 800; color: #0f172a; margin: 8px 0 4px 0; }
        .notes-box blockquote { border-left: 3px solid {{ $th['500'] }}; padding-left: 8px; color: #64748b; margin: 4px 0; font-style: italic; }
        .notes-box a { color: {{ $th['600'] }}; text-decoration: underline; }
        .notes-box hr { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }

        /* ------------------------------------------------------------- */
        /* Footer */
        /* ------------------------------------------------------------- */
        .doc-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid {{ $th['100'] }};
            font-size: 9px;
            color: #94a3b8;
            width: 100%;
        }

        .doc-footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-footer td {
            padding: 0;
        }
    </style>
</head>
<body>

    <div class="top-accent-bar"></div>

    {{-- Document Header --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                <span class="brand-badge">Official Meeting Report</span>
                <h1 class="doc-title">{{ $meeting->club->name }}</h1>
                <div class="doc-subtitle">Executive Meeting Minutes & Attendance Record</div>
            </td>
            <td class="header-right">
                <div class="meeting-num-pill">Meeting #{{ $meeting->meeting_number }}</div>
            </td>
        </tr>
    </table>

    {{-- Meeting Details Card --}}
    <table class="info-card">
        <tr>
            <td style="width: 25%;">
                <div class="info-label">Meeting Date</div>
                <div class="info-value">{{ $meeting->meeting_date->format('l, d F Y') }}</div>
                @if($meeting->formattedTime())
                    <div style="font-size: 9.5px; color: #64748b; font-weight: 600; margin-top: 2px;">{{ $meeting->formattedTime() }}</div>
                @endif
            </td>
            <td style="width: 25%;">
                <div class="info-label">Venue / Location</div>
                <div class="info-value">{{ $meeting->venue ?: 'Regular Meeting Venue' }}</div>
            </td>
            <td style="width: 25%;">
                <div class="info-label">Status</div>
                <div>
                    <span class="status-badge status-{{ strtolower($meeting->status) }}">
                        {{ ucfirst($meeting->status) }}
                    </span>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="info-label">Total Roll Call</div>
                <div class="info-value">{{ $stats['total'] }} Registered Members</div>
            </td>
        </tr>
    </table>

    {{-- Meeting Theme --}}
    @if($meeting->theme)
    <div class="theme-box">
        <div class="theme-box-label">Meeting Theme & Thought of the Day</div>
        <div class="theme-box-title">"{{ $meeting->theme }}"</div>
    </div>
    @endif

    {{-- Attendance Summary Metrics --}}
    @if(!$meeting->attendance->isEmpty())
    <div class="section-container">
        <div class="section-heading">Attendance Statistics</div>
        <table class="stats-table">
            <tr>
                <td class="stat-card present">
                    <div class="stat-num">{{ $stats['present'] }}</div>
                    <div class="stat-text">Present</div>
                </td>
                <td class="stat-card absent">
                    <div class="stat-num">{{ $stats['absent'] }}</div>
                    <div class="stat-text">Absent</div>
                </td>
                <td class="stat-card late">
                    <div class="stat-num">{{ $stats['late'] }}</div>
                    <div class="stat-text">Late</div>
                </td>
                <td class="stat-card excused">
                    <div class="stat-num">{{ $stats['excused'] }}</div>
                    <div class="stat-text">Excused</div>
                </td>
                <td class="stat-card rate">
                    <div class="stat-num">{{ $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0 }}%</div>
                    <div class="stat-text">Attendance Rate</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Two Column Section: Meeting Roles & Table Topics --}}
    <table class="two-column-table section-container">
        <tr>
            {{-- Left Column: Meeting Roles --}}
            <td>
                <div class="section-heading">Meeting Role Bearers</div>
                @if($meeting->roles->isEmpty())
                    <p style="color: #94a3b8; font-style: italic; margin: 6px 0;">No roles assigned for this meeting.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Role</th>
                                <th style="width: 55%;">Role Bearer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                            <tr>
                                <td class="role-title">{{ $role->roleType->name }}</td>
                                <td class="user-name">{{ $role->user->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </td>

            {{-- Right Column: Table Topics --}}
            <td>
                <div class="section-heading">Table Topics Speakers</div>
                @if($meeting->ttmSpeakers->isEmpty())
                    <p style="color: #94a3b8; font-style: italic; margin: 6px 0;">No Table Topics speakers recorded.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 12%; text-align: center;">Slot</th>
                                <th style="width: 38%;">Speaker</th>
                                <th style="width: 32%;">Topic / Remarks</th>
                                <th style="width: 18%; text-align: right;">Timing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->ttmSpeakers as $ttm)
                            @php
                                $ttmTimer = $meeting->timerLogs->where('speaker_type', 'ttm_speaker')->firstWhere('reference_id', $ttm->id);
                            @endphp
                            <tr>
                                <td style="text-align: center;"><span class="slot-badge">#{{ $ttm->slot }}</span></td>
                                <td class="user-name">{{ $ttm->user->name }}</td>
                                <td style="color: #64748b;">{{ $ttm->topic ?: '—' }}</td>
                                <td style="text-align: right; color: #475569; font-weight: 600;">
                                    @if($ttmTimer && $ttmTimer->time_taken)
                                        <div style="font-size: 10px; font-weight: 800; color: #0f172a;">{{ $ttmTimer->time_taken }}</div>
                                        <span class="timer-badge timer-{{ str_replace('_', '', $ttmTimer->status == 'within_time' ? 'within' : ($ttmTimer->status == 'over_time' ? 'over' : ($ttmTimer->status == 'under_time' ? 'under' : 'dq'))) }}">
                                            {{ $ttmTimer->statusBadge()['label'] }}
                                        </span>
                                    @else
                                        {{ $ttm->formattedTiming() }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    {{-- Prepared Speeches & Evaluations --}}
    <div class="section-container">
        <div class="section-heading">Prepared Speeches & Evaluations</div>
        @if($meeting->speakers->isEmpty())
            <p style="color: #94a3b8; font-style: italic; margin: 6px 0;">No prepared speeches recorded for this meeting.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Speaker</th>
                        <th style="width: 22%;">Project / Level</th>
                        <th style="width: 28%;">Speech Title</th>
                        <th style="width: 12%; text-align: center;">Timing</th>
                        <th style="width: 18%;">Evaluator</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meeting->speakers as $speaker)
                    @php
                        $spkTimer = $meeting->timerLogs->where('speaker_type', 'prepared_speaker')->firstWhere('reference_id', $speaker->id);
                    @endphp
                    <tr>
                        <td class="user-name">{{ $speaker->user->name }}</td>
                        <td style="color: {{ $th['700'] }}; font-weight: 600;">{{ $speaker->speech_type ?: 'Prepared Speech' }}</td>
                        <td style="color: #0f172a; font-weight: 600;">{{ $speaker->topic ? '"' . $speaker->topic . '"' : '—' }}</td>
                        <td style="text-align: center; color: #475569; font-weight: 600;">
                            @if($spkTimer && $spkTimer->time_taken)
                                <div style="font-size: 10px; font-weight: 800; color: #0f172a;">{{ $spkTimer->time_taken }}</div>
                                <span class="timer-badge timer-{{ str_replace('_', '', $spkTimer->status == 'within_time' ? 'within' : ($spkTimer->status == 'over_time' ? 'over' : ($spkTimer->status == 'under_time' ? 'under' : 'dq'))) }}">
                                    {{ $spkTimer->statusBadge()['label'] }}
                                </span>
                            @else
                                <span class="slot-badge">{{ $speaker->formattedTiming() }}</span>
                            @endif
                        </td>
                        <td style="color: #059669; font-weight: 700;">
                            {{ $speaker->evaluation ? $speaker->evaluation->evaluator->name : 'Not Assigned' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Official Timer Report --}}
    @php
        $hasAnyTimerLog = $meeting->timerLogs->whereNotNull('time_taken')->isNotEmpty();
    @endphp
    @if($hasAnyTimerLog || $meeting->speakers->isNotEmpty() || $meeting->evaluations->isNotEmpty() || $meeting->ttmSpeakers->isNotEmpty())
    <div class="section-container">
        <div class="section-heading">Official Timer Report</div>
        <table class="data-table" style="margin-bottom: 6px;">
            <thead>
                <tr>
                    <th style="width: 18%;">Category</th>
                    <th style="width: 26%;">Speaker / Role Bearer</th>
                    <th style="width: 24%;">Speech / Detail</th>
                    <th style="width: 12%; text-align: center;">Allotted</th>
                    <th style="width: 10%; text-align: center;">Actual Time</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                {{-- Prepared Speakers --}}
                @foreach($meeting->speakers as $speaker)
                @php
                    $tLog = $meeting->timerLogs->where('speaker_type', 'prepared_speaker')->firstWhere('reference_id', $speaker->id);
                @endphp
                <tr>
                    <td style="font-weight: 700; color: {{ $th['700'] }}; font-size: 9px;">Prepared Speech</td>
                    <td class="user-name">{{ $speaker->user->name }}</td>
                    <td style="color: #64748b; font-size: 9.5px;">{{ $speaker->topic ? '"' . $speaker->topic . '"' : ($speaker->speech_type ?: 'Speech') }}</td>
                    <td style="text-align: center; color: #64748b;">{{ $speaker->formattedTiming() }}</td>
                    <td style="text-align: center; font-weight: 800; color: #0f172a;">
                        {{ $tLog && $tLog->time_taken ? $tLog->time_taken : '—' }}
                    </td>
                    <td style="text-align: center;">
                        @if($tLog && $tLog->time_taken)
                            <span class="timer-badge timer-{{ str_replace('_', '', $tLog->status == 'within_time' ? 'within' : ($tLog->status == 'over_time' ? 'over' : ($tLog->status == 'under_time' ? 'under' : 'dq'))) }}">
                                {{ $tLog->statusBadge()['label'] }}
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 8.5px;">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach

                {{-- Speech Evaluators --}}
                @foreach($meeting->evaluations as $evaluation)
                @php
                    $eLog = $meeting->timerLogs->where('speaker_type', 'evaluator')->firstWhere('reference_id', $evaluation->id);
                @endphp
                <tr>
                    <td style="font-weight: 700; color: #059669; font-size: 9px;">Speech Evaluation</td>
                    <td class="user-name">{{ $evaluation->evaluator->name }}</td>
                    <td style="color: #64748b; font-size: 9.5px;">Eval for {{ $evaluation->speaker ? $evaluation->speaker->user->name : 'Speaker' }}</td>
                    <td style="text-align: center; color: #64748b;">2-3 mins</td>
                    <td style="text-align: center; font-weight: 800; color: #0f172a;">
                        {{ $eLog && $eLog->time_taken ? $eLog->time_taken : '—' }}
                    </td>
                    <td style="text-align: center;">
                        @if($eLog && $eLog->time_taken)
                            <span class="timer-badge timer-{{ str_replace('_', '', $eLog->status == 'within_time' ? 'within' : ($eLog->status == 'over_time' ? 'over' : ($eLog->status == 'under_time' ? 'under' : 'dq'))) }}">
                                {{ $eLog->statusBadge()['label'] }}
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 8.5px;">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach

                {{-- Table Topics Speakers --}}
                @foreach($meeting->ttmSpeakers as $ttm)
                @php
                    $ttmLog = $meeting->timerLogs->where('speaker_type', 'ttm_speaker')->firstWhere('reference_id', $ttm->id);
                @endphp
                <tr>
                    <td style="font-weight: 700; color: #9333ea; font-size: 9px;">Table Topic (#{{ $ttm->slot }})</td>
                    <td class="user-name">{{ $ttm->user->name }}</td>
                    <td style="color: #64748b; font-size: 9.5px;">{{ $ttm->topic ?: 'Table Topic Speech' }}</td>
                    <td style="text-align: center; color: #64748b;">{{ $ttm->formattedTiming() }}</td>
                    <td style="text-align: center; font-weight: 800; color: #0f172a;">
                        {{ $ttmLog && $ttmLog->time_taken ? $ttmLog->time_taken : '—' }}
                    </td>
                    <td style="text-align: center;">
                        @if($ttmLog && $ttmLog->time_taken)
                            <span class="timer-badge timer-{{ str_replace('_', '', $ttmLog->status == 'within_time' ? 'within' : ($ttmLog->status == 'over_time' ? 'over' : ($ttmLog->status == 'under_time' ? 'under' : 'dq'))) }}">
                                {{ $ttmLog->statusBadge()['label'] }}
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 8.5px;">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Attendance Roll Call List --}}
    @if(!$meeting->attendance->isEmpty())
    <div class="section-container">
        <div class="section-heading">Attendance Record</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Member Name</th>
                    <th style="width: 30%;">Email Address</th>
                    <th style="width: 30%;">Attendance Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meeting->attendance->sortBy('user.name') as $att)
                <tr>
                    <td class="user-name">{{ $att->user->name }}</td>
                    <td style="color: #64748b;">{{ $att->user->email }}</td>
                    <td>
                        @if($att->status === 'present')
                            <span class="att-badge att-present"><span class="att-icon">&#10003;</span> Present</span>
                        @elseif($att->status === 'late')
                            <span class="att-badge att-late"><span class="att-icon">&#9679;</span> Late</span>
                        @elseif($att->status === 'excused')
                            <span class="att-badge att-excused"><span class="att-icon">&mdash;</span> Excused</span>
                        @else
                            <span class="att-badge att-absent"><span class="att-icon">&#10007;</span> Absent</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Meeting Notes & Takeaways --}}
    @if($meeting->notes)
    <div class="section-container">
        <div class="section-heading">Meeting Minutes & Secretary Notes</div>
        <div class="notes-box">{!! $meeting->notes !!}</div>
    </div>
    @endif

    {{-- Document Footer --}}
    <div class="doc-footer">
        <table>
            <tr>
                <td style="text-align: left;">
                    Generated by Speech Club Management System &bull; {{ $meeting->club->name }}
                </td>
                <td style="text-align: right;">
                    Generated on {{ now()->format('d M Y, h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
