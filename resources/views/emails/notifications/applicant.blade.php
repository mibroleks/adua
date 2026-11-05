{{--
|--------------------------------------------------------------------------
| Applicant Notification Email
|--------------------------------------------------------------------------
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Branded email presentation for applicant-facing notifications.
|
| Important:
| - Uses the institution's existing ThemeService branding.
| - Uses inline styles for email-client compatibility.
| - Does not control password-reset or unrelated system emails.
| - Presentation only; notification logic remains in GenericNotification.
|--------------------------------------------------------------------------
--}}

@php
    $primary = $tokens['primary'] ?? '#73152A';
    $primaryDeep = $tokens['primary_deep'] ?? '#4A0D1A';
    $accent = $tokens['accent'] ?? '#B99A62';
    $page = $tokens['page'] ?? '#F7F5F1';
    $surface = $tokens['surface'] ?? '#FFFFFF';
    $heading = $tokens['heading'] ?? '#26221F';
    $body = $tokens['body'] ?? '#49433F';
    $muted = $tokens['muted'] ?? '#77716B';
    $border = $tokens['border'] ?? '#E8E2DC';
    $footerBg = $tokens['footer_bg'] ?? $primaryDeep;
    $footerText = $tokens['footer_text'] ?? '#FFFFFF';
    $footerMuted = $tokens['footer_muted'] ?? '#D4CBC5';
    $buttonBg = $tokens['button_primary_bg'] ?? $primary;
    $buttonText = $tokens['button_primary_text'] ?? '#FFFFFF';

    $font = $fontFamily ?: 'Arial, Helvetica, sans-serif';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <title>{{ $subject }}</title>
</head>

<body style="margin:0; padding:0; width:100%; background-color:{{ $page }}; color:{{ $body }}; font-family:{{ $font }}, Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%;">

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="width:100%; background-color:{{ $page }}; border-collapse:collapse;"
>
    <tr>
        <td align="center" style="padding:36px 16px;">

            <table
                role="presentation"
                width="600"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="width:100%; max-width:600px; border-collapse:separate; border-spacing:0;"
            >

                {{-- Institutional header --}}
                <tr>
                    <td
                        align="center"
                        style="padding:28px 24px; background-color:{{ $primaryDeep }}; border-radius:12px 12px 0 0;"
                    >

                        @if($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $institutionName }}"
                                style="display:block; width:auto; max-width:180px; max-height:76px; margin:0 auto 14px; border:0; outline:none; text-decoration:none;"
                            >
                        @else
                            <div style="display:inline-block; margin-bottom:12px; color:#FFFFFF; font-size:25px; font-weight:800; letter-spacing:1px;">
                                {{ $shortName }}
                            </div>
                        @endif

                        <div style="color:#FFFFFF; font-size:19px; line-height:1.4; font-weight:700;">
                            {{ $institutionName }}
                        </div>

                        <div style="width:44px; height:3px; margin:14px auto 0; background-color:{{ $accent }};"></div>

                    </td>
                </tr>

                {{-- Main notification content --}}
                <tr>
                    <td
                        style="padding:36px 34px 32px; background-color:{{ $surface }}; border-left:1px solid {{ $border }}; border-right:1px solid {{ $border }};"
                    >

                        <div style="margin-bottom:10px; color:{{ $primary }}; font-size:11px; line-height:1.4; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;">
                            OFFICIAL APPLICANT NOTIFICATION
                        </div>

                        <h1 style="margin:0 0 22px; color:{{ $heading }}; font-size:25px; line-height:1.3; font-weight:750;">
                            {{ $title }}
                        </h1>

                        <p style="margin:0 0 18px; color:{{ $body }}; font-size:15px; line-height:1.8;">
                            {{ $greeting }}
                        </p>

                        <div style="margin:0 0 20px; color:{{ $body }}; font-size:15px; line-height:1.85; white-space:pre-line; overflow-wrap:anywhere;">
                            {{ $notificationMessage }}
                        </div>

                        @if(!empty($emailLines))
                            @foreach($emailLines as $line)
                                @if(filled($line))
                                    <p style="margin:0 0 14px; color:{{ $body }}; font-size:14px; line-height:1.8; overflow-wrap:anywhere;">
                                        {{ $line }}
                                    </p>
                                @endif
                            @endforeach
                        @endif

                        {{-- Optional portal action --}}
                        @if($actionUrl)
                            <table
                                role="presentation"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="margin:28px 0 24px; border-collapse:collapse;"
                            >
                                <tr>
                                    <td
                                        align="center"
                                        bgcolor="{{ $buttonBg }}"
                                        style="border-radius:7px;"
                                    >
                                        <a
                                            href="{{ $actionUrl }}"
                                            target="_blank"
                                            style="display:inline-block; padding:14px 24px; border:1px solid {{ $buttonBg }}; border-radius:7px; background-color:{{ $buttonBg }}; color:{{ $buttonText }}; font-size:14px; line-height:1.3; font-weight:700; text-decoration:none;"
                                        >
                                            {{ $actionText }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        {{-- Salutation --}}
                        <p style="margin:28px 0 0; padding-top:20px; border-top:1px solid {{ $border }}; color:{{ $muted }}; font-size:13px; line-height:1.7;">
                            {{ $salutation }}
                        </p>

                    </td>
                </tr>

                {{-- Institutional footer --}}
                <tr>
                    <td
                        style="padding:24px 28px; background-color:{{ $footerBg }}; border-radius:0 0 12px 12px;"
                    >

                        <div style="margin-bottom:12px; color:{{ $footerText }}; font-size:14px; line-height:1.5; font-weight:700;">
                            {{ $institutionName }}
                        </div>

                        @if($address)
                            <div style="margin-bottom:8px; color:{{ $footerMuted }}; font-size:12px; line-height:1.7;">
                                {{ $address }}
                            </div>
                        @endif

                        @if($institutionEmail || $institutionPhone)
                            <div style="color:{{ $footerMuted }}; font-size:12px; line-height:1.9;">

                                @if($institutionEmail)
                                    <span>
                                        Email:
                                        <a
                                            href="mailto:{{ $institutionEmail }}"
                                            style="color:{{ $footerText }}; text-decoration:underline;"
                                        >
                                            {{ $institutionEmail }}
                                        </a>
                                    </span>
                                @endif

                                @if($institutionPhone)
                                    @if($institutionEmail)
                                        <span style="padding:0 5px;">|</span>
                                    @endif

                                    <span>
                                        Phone: {{ $institutionPhone }}
                                    </span>
                                @endif

                            </div>
                        @endif

                        @if($website)
                            <div style="margin-top:8px; color:{{ $footerMuted }}; font-size:12px; line-height:1.7;">
                                {{ $website }}
                            </div>
                        @endif

                        <div style="margin-top:20px; padding-top:14px; border-top:1px solid {{ $footerMuted }}; color:{{ $footerMuted }}; font-size:11px; line-height:1.7;">
                            This is an official notification from {{ $institutionName }}.
                            Please do not reply if this mailbox is not monitored.
                        </div>

                    </td>
                </tr>

            </table>

            {{-- Platform attribution --}}
            <div style="max-width:600px; margin:16px auto 0; color:{{ $muted }}; font-size:11px; line-height:1.6; text-align:center;">
                Powered by Ygrace Tech
            </div>

        </td>
    </tr>
</table>

</body>
</html>
