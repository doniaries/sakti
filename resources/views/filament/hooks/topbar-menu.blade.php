@php
    $headerMenus = \App\Filament\MenuBuilder\SidebarNavigationService::getHeaderMenus();
@endphp

@if ($headerMenus !== [])
    <nav style="display: flex; align-items: center; gap: 0.375rem; margin-right: 0.75rem;" aria-label="Header Navigasi">
        @foreach ($headerMenus as $menu)
            <x-filament::dropdown placement="bottom-start" max-height="24rem">
                <x-slot name="trigger">
                    <x-filament::button
                        color="gray"
                        size="sm"
                        outlined
                        :icon="$menu['icon'] ?: 'heroicon-o-bars-3'"
                        icon-position="before">
                        {{ $menu['name'] }}
                    </x-filament::button>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach ($menu['items'] as $item)
                        @if ($item['children'] !== [])
                            <x-filament::dropdown.header :icon="$item['icon'] ?: 'heroicon-o-folder'">
                                {{ $item['title'] }}
                            </x-filament::dropdown.header>
                            @foreach ($item['children'] as $child)
                                @if (filled($child['url']))
                                    <x-filament::dropdown.list.item
                                        tag="a"
                                        :href="$child['url']"
                                        :target="$child['target'] === '_blank' ? '_blank' : null"
                                        :color="$child['active'] ? 'primary' : 'gray'"
                                        :icon="$child['icon'] ?: 'heroicon-o-chevron-right'">
                                        {{ $child['title'] }}
                                    </x-filament::dropdown.list.item>
                                @endif
                            @endforeach
                        @elseif (filled($item['url']))
                            <x-filament::dropdown.list.item
                                tag="a"
                                :href="$item['url']"
                                :target="$item['target'] === '_blank' ? '_blank' : null"
                                :color="$item['active'] ? 'primary' : 'gray'"
                                :icon="$item['icon'] ?: 'heroicon-o-link'">
                                {{ $item['title'] }}
                            </x-filament::dropdown.list.item>
                        @endif
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        @endforeach
    </nav>
@endif

