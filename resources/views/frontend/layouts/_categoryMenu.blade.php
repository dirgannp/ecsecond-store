@php
    $menu = Helper::getAllParentWithChild();
@endphp
@if($menu->isNotEmpty())
    <li>
        <a href="javascript:void(0);">Category<i class="ti-angle-down"></i></a>
        <ul class="dropdown border-0 shadow">
            @foreach($menu as $cat_info)
                @if($cat_info->child_cat->count() > 0)
                    <li>
                        <a href="{{ route('product-cat', $cat_info->slug) }}">{{ $cat_info->title }}</a>
                        <ul class="dropdown sub-dropdown border-0 shadow">
                            @foreach($cat_info->child_cat as $sub_menu)
                                <li>
                                    <a href="{{ route('product-sub-cat', [$cat_info->slug, $sub_menu->slug]) }}">{{ $sub_menu->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li>
                        <a href="{{ route('product-cat', $cat_info->slug) }}">{{ $cat_info->title }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
    </li>
@endif