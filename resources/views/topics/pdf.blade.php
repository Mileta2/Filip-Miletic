<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>{{ $topic->title }}</title>
    <style>
        @page { margin: 34px 42px 44px; }
        * { box-sizing: border-box; }
        body {
            color: #102a43;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.55;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #1178d5;
            margin-bottom: 28px;
            padding-bottom: 15px;
        }
        .header-table { border-collapse: collapse; width: 100%; }
        .logo-cell { vertical-align: middle; width: 64px; }
        .logo { height: 50px; width: 50px; }
        .faculty { color: #062949; font-size: 15px; font-weight: bold; }
        .system-name { color: #526d82; font-size: 9px; margin-top: 2px; }
        .document-label {
            background: #eaf5ff;
            border-radius: 4px;
            color: #1178d5;
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .5px;
            margin-bottom: 10px;
            padding: 5px 9px;
            text-transform: uppercase;
        }
        h1 { color: #062949; font-size: 22px; line-height: 1.3; margin: 0 0 22px; }
        h2 {
            border-bottom: 1px solid #d9e2ec;
            color: #062949;
            font-size: 13px;
            margin: 25px 0 12px;
            padding-bottom: 6px;
        }
        .details { border-collapse: collapse; width: 100%; }
        .details td { border-bottom: 1px solid #edf2f7; padding: 7px 6px; vertical-align: top; }
        .details td:first-child { color: #526d82; font-weight: bold; width: 31%; }
        .description { text-align: justify; white-space: pre-line; }
        .committee { margin: 0; padding-left: 18px; }
        .committee li { margin-bottom: 5px; }
        .footer {
            bottom: 8px;
            color: #7b8794;
            font-size: 8px;
            left: 42px;
            position: fixed;
            right: 42px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img class="logo" src="{{ public_path('images/logo-ftn-icon.png') }}" alt="">
                </td>
                <td>
                    <div class="faculty">{{ config('app.faculty_name') }}</div>
                    <div class="system-name">Sistem za izbor tema diplomskih i master radova</div>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <div class="document-label">{{ $topic->type->label() }}</div>
        <h1>{{ $topic->title }}</h1>

        <table class="details">
            <tr><td>Predmet</td><td>{{ $topic->course }}</td></tr>
            <tr><td>Status</td><td>{{ $topic->status->label() }}</td></tr>
            <tr><td>Mentor</td><td>{{ trim(($topic->mentor->professorProfile?->academic_title ?? '').' '.$topic->mentor->name) }}</td></tr>
            @if($topic->student)
                <tr><td>Student</td><td>{{ $topic->student->name }}@if($topic->student->studentProfile) ({{ $topic->student->studentProfile->index_number }})@endif</td></tr>
            @endif
            @if($topic->reserved_at)
                <tr><td>Datum rezervacije</td><td>{{ $topic->reserved_at->format('d.m.Y.') }}</td></tr>
            @endif
            @if($topic->defended_at)
                <tr><td>Datum odbrane</td><td>{{ $topic->defended_at->format('d.m.Y.') }}</td></tr>
            @endif
        </table>

        <h2>Opis teme</h2>
        <div class="description">{{ $topic->description }}</div>

        @if($topic->committeeMembers->isNotEmpty())
            <h2>Komisija</h2>
            <ul class="committee">
                @foreach($topic->committeeMembers as $member)
                    <li><strong>{{ $member->role->label() }}:</strong> {{ $member->professor->name }}</li>
                @endforeach
            </ul>
        @endif
    </main>

    <div class="footer">
        Dokument je generisan {{ now()->format('d.m.Y. \u H:i') }} putem informacionog sistema fakulteta.
    </div>
</body>
</html>
