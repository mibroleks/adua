{{-- 
Component: Portal Breadcrumbs
File Path: resources/views/components/portal-breadcrumbs.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable breadcrumb navigation for the student portal.
Displays page hierarchy and context in the portal header.
Ensures consistent navigation cues across all views.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@props([
    'items' => [], // array of ['label' => 'Dashboard', 'route' => 'dashboard']
])

<nav
    {{ $attributes->merge(['class' => 'portal-breadcrumbs']) }}
    aria-label="Breadcrumb"
>
    <ol class="portal-breadcrumbs__list">

        @foreach($items as $index => $item)

            <li class="portal-breadcrumbs__item">

                @if(!empty($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="portal-breadcrumbs__link"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span
                        class="portal-breadcrumbs__current"
                        aria-current="page"
                    >
                        {{ $item['label'] }}
                    </span>
                @endif

                @if($index < count($items) - 1)
                    <span
                        class="portal-breadcrumbs__separator"
                        aria-hidden="true"
                    >
                        /
                    </span>
                @endif

            </li>

        @endforeach

    </ol>
</nav>
