@extends('layouts.hims')

@section('title', 'Profile')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Profile</h1>
            <p class="mt-1 text-sm text-slate-500">Update your personal information and account security.</p>
        </div>
    </div>

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="max-w-3xl space-y-8">
            {{-- Two-Factor Authentication status --}}
            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-full {{ auth()->user()->hasMfaEnabled() ? 'bg-teal-50 text-teal-600' : 'bg-amber-50 text-amber-600' }} p-3">
                        <i class="bi bi-shield-lock text-xl"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800">Two-Factor Authentication</div>
                        <div class="text-sm {{ auth()->user()->hasMfaEnabled() ? 'text-teal-600' : 'text-amber-600' }}">
                            {{ auth()->user()->hasMfaEnabled() ? 'Enabled — your account is protected.' : 'Not enabled — add an extra security layer.' }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('mfa.setup') }}" class="shrink-0 px-4 py-2 text-sm font-medium {{ auth()->user()->hasMfaEnabled() ? 'border border-slate-300 text-slate-700 hover:bg-slate-100' : 'bg-teal-600 text-white hover:bg-teal-700' }} rounded-lg">
                    {{ auth()->user()->hasMfaEnabled() ? 'Manage' : 'Set up' }}
                </a>
            </div>

            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
