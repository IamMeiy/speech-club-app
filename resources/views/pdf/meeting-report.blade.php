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

        .brand-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            font-weight: bold;
            font-size: 9px;
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
            background: #4f46e5;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 8px;
            letter-spacing: -0.5px;
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

        /* ------------------------------------------------------------- */
        /* Theme Box */
        /* ------------------------------------------------------------- */
        .theme-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 9px 14px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 16px;
        }

        .theme-box-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #2563eb;
            font-weight: 700;
        }

        .theme-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 2px;
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
        .stat-card.rate { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }

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
            color: #334155;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
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
            background: #f3e8ff;
            color: #7e22ce;
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
            white-space: pre-wrap;
            line-height: 1.5;
        }

        /* ------------------------------------------------------------- */
        /* Footer */
        /* ------------------------------------------------------------- */
        .doc-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
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
                                <th style="width: 15%; text-align: center;">Slot</th>
                                <th style="width: 45%;">Speaker</th>
                                <th style="width: 40%;">Topic / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->ttmSpeakers as $ttm)
                            <tr>
                                <td style="text-align: center;"><span class="slot-badge">#{{ $ttm->slot }}</span></td>
                                <td class="user-name">{{ $ttm->user->name }}</td>
                                <td style="color: #64748b;">{{ $ttm->topic ?: '—' }}</td>
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
                        <th style="width: 25%;">Speaker</th>
                        <th style="width: 25%;">Project / Level</th>
                        <th style="width: 30%;">Speech Title</th>
                        <th style="width: 20%;">Evaluator</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meeting->speakers as $speaker)
                    <tr>
                        <td class="user-name">{{ $speaker->user->name }}</td>
                        <td style="color: #4338ca; font-weight: 600;">{{ $speaker->speech_type ?: 'Prepared Speech' }}</td>
                        <td style="color: #0f172a; font-weight: 600;">{{ $speaker->topic ? '"' . $speaker->topic . '"' : '—' }}</td>
                        <td style="color: #059669; font-weight: 700;">
                            {{ $speaker->evaluation ? $speaker->evaluation->evaluator->name : 'Not Assigned' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

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
                            <span style="color: #15803d; font-weight: 700;">✓ Present</span>
                        @elseif($att->status === 'late')
                            <span style="color: #b45309; font-weight: 700;">⏱ Late</span>
                        @elseif($att->status === 'excused')
                            <span style="color: #475569; font-weight: 600;">— Excused</span>
                        @else
                            <span style="color: #be123c; font-weight: 700;">✗ Absent</span>
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
        <div class="notes-box">{{ $meeting->notes }}</div>
    </div>
    @endif

    {{-- Document Footer --}}
    <div class="doc-footer">
        <table>
            <tr>
                <td style="text-align: left;">
                    Generated by Speech Club Management System · {{ $meeting->club->name }}
                </td>
                <td style="text-align: right;">
                    Generated on {{ now()->format('d M Y, h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
