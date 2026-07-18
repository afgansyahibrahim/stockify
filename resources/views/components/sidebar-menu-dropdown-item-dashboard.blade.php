@props([
    'routeName',
    'title'
])

<li>
    <a href="{{ route($routeName) }}"
        class="flex items-center w-full p-2 pl-11 text-base text-gray-900 rounded-lg hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700
        {{ request()->routeIs($routeName) ? 'bg-gray-100 dark:bg-gray-700' : '' }}">

        {{ $title }}

    </a>
</li>