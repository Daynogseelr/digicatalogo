<div class="text-center">
    @php
        $hasImage = false;
    @endphp

    @for ($i = 1; $i <= 3; $i++)
        @php
            $currentUrl = ${'url' . $i};
        @endphp

        @if ($currentUrl)
            {{-- Verificamos si la URL actual existe --}}
            @php
                $hasImage = true; // Marcamos que al menos una imagen existe
            @endphp
            <img src="{{ asset('storage/' . $currentUrl) }}" width="50px" height="50px"
                alt="Product Image {{ $i }}" onerror="this.src='{{ asset('products/product.png') }}'">
        @endif
    @endfor

    @if (!$hasImage)
        {{-- Si ninguna de las URLs de las imágenes existió, mostramos la imagen de placeholder --}}
        <img src="{{ asset('products/product.png') }}" width="50px" height="50px" alt="Default Product Image">
    @endif
</div>
