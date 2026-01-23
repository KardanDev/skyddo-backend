@component('mail::message')
{{-- Header with Company Branding --}}
<div style="text-align: center; margin-bottom: 32px;">
@if(isset($companySettings) && $companySettings->logo_url)
    <img src="{{ $companySettings->logo_url }}" alt="{{ $companySettings->company_name ?? config('app.name') }}" style="max-height: 60px; margin: 0 auto;">
@else
    <div style="font-size: 28px; font-weight: bold; color: #1a202c; letter-spacing: -0.5px;">
        Skyddo Admin
    </div>
@endif
</div>

{{-- Main Content --}}
# You're Invited to Join the Team! 🎉

Hello **{{ $invitation->email }}**,

**{{ $inviter->name }}** has invited you to join {{ isset($companySettings) ? $companySettings->company_name : config('app.name') }} as a **{{ ucfirst(str_replace('_', ' ', $invitation->role)) }}**.

We're excited to have you on board! Click the button below to accept your invitation and complete your registration.

@component('mail::button', ['url' => $registerUrl, 'color' => 'primary'])
Accept Invitation & Register
@endcomponent

{{-- Role Information --}}
<div style="background-color: #f7fafc; border-left: 4px solid #4299e1; padding: 16px; margin: 24px 0; border-radius: 4px;">
<strong>Your Role:</strong> {{ ucfirst(str_replace('_', ' ', $invitation->role)) }}<br>
<strong>Access Level:</strong>
@if($invitation->role === 'super_user')
Full system access with administrative privileges
@elseif($invitation->role === 'admin')
Manage team members, settings, and all operations
@else
Manage clients, quotes, policies, and claims
@endif
</div>

{{-- Important Information --}}
⏰ **Please note:** This invitation will expire on **{{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}**.

{{-- Footer Message --}}
<div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; color: #718096; font-size: 14px;">
If you did not expect this invitation or have any questions, please contact **{{ $inviter->name }}** at {{ $inviter->email }}.
</div>

Thanks,<br>
**{{ isset($companySettings) ? $companySettings->company_name : config('app.name') }} Team**

{{-- Security Notice --}}
<x-mail::subcopy>
For security reasons, this invitation link is unique to your email address and can only be used once. Do not share this link with others.
</x-mail::subcopy>
@endcomponent
