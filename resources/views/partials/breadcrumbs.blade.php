<nav class="breadcrumb container py-3 mb-5 bg-gray-800">
    <a class="breadcrumb-item text-neutral-50 text-2xl font-semibold mt-12 mb-12" href="{{ route('homepage') }}">
    <i class="fas fa-home"></i> Home  > </a>

    @foreach($path as $url_name=>$url)
        @if($url == '')
            <span class="breadcrumb-item active text-neutral-50 text-2xl font-semibold mt-12 mb-12"> {{ $url_name }} </span>
        @else
            <a class="breadcrumb-item text-neutral-50 text-2xl font-semibold mt-12 mb-12" href="{{ url($url) }}">{{ $url_name }}</a>
        @endif
    @endforeach
</nav>