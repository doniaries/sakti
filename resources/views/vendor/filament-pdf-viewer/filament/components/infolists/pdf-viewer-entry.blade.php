<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="fi-sc-flex" style="width: 100%;">
        @if(!empty($getState()))
            <iframe class="fi-growable" src="{{ $getRoute(is_array($getState()) ? current($getState()) : $getState()) }}"
                    style="width: 100%; min-height: {{ $getMinHeight() }}; border: none; border-radius: 8px; background-color: #fff;"></iframe>
        @elseif(!empty($getFileUrl()))
            <iframe class="fi-growable" src="{{ $getFileUrl() }}" style="width: 100%; min-height: {{ $getMinHeight() }}; border: none; border-radius: 8px; background-color: #fff;"></iframe>
        @endif
    </div>
</x-dynamic-component>
