{{-- 
Component: Portal User Menu
File Path: resources/views/components/portal-user-menu.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable dropdown menu for the student portal.
Provides quick access to profile, notifications, support, and logout.
Ensures consistent navigation and accessibility.

Status: ✅ Production Ready
Version: 1.0
--}}

@php
    $user = auth()->user();
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }">
    {{-- Trigger button --}}
    <button
        type="button"
        class="flex items-center gap-2 rounded-full bg-[var(--theme-surface)] px-3 py-2 text-sm font-medium theme-text hover:bg-[var(--theme-muted-soft)]"
        @click="open = !open"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <span class="hidden sm:inline">{{ $user->name }}</span>
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[var(--theme-info-soft)] text-[var(--theme-info)] font-bold">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </span>
    </button>

    {{-- Dropdown menu --}}
    <div
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-48 origin-top-right rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface)] shadow-lg focus:outline-none z-50"
        role="menu"
        aria-orientation="vertical"
    >
        <div class="py-1">
            <a href="{{ route('profile') }}" class="portal-user-menu__item" role="menuitem">Profile</a>
            <a href="{{ route('notifications.index') }}" class="portal-user-menu__item" role="menuitem">Notifications</a>
            <a href="{{ route('support.index') }}" class="portal-user-menu__item" role="menuitem">Support</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="portal-user-menu__item w-full text-left" role="menuitem">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
