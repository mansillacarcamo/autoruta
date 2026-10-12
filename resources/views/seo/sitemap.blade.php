{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $u)
  <url>
    <loc>{{ $u['loc'] }}</loc>
    @if (! empty($u['modificado']))<lastmod>{{ $u['modificado'] }}</lastmod>@endif
    <priority>{{ $u['prioridad'] }}</priority>
  </url>
@endforeach
</urlset>
