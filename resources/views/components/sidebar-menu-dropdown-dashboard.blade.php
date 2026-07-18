@props([
    'icon' => null,
    'id' => null,
    'activeRoute' => null,
    'title' => null
])

<li>
    <button
        type="button"
        data-collapse-toggle="{{ $id }}"
        aria-controls="{{ $id }}"
        class="flex items-center w-full p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">

        {{ $icon }}

        <span class="flex-1 ml-3 text-left whitespace-nowrap">
            {{ $title }}
        </span>

        <svg class="w-5 h-5 transition-transform"
            fill="currentColor"
            viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"/>
        </svg>
    </button>

    <ul
        id="{{ $id }}"
        class="{{ request()->routeIs($activeRoute) ? 'block' : 'hidden' }} py-2 space-y-2">

        {{ $slot }}

    </ul>
</li>