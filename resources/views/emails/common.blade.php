<x-mail::message>
# {{ $contentData['title'] }}

<p>
{{ $contentData['message'] }}
</p>

@lang('Regards'),<br>
{{ config('app.name') }}
</x-mail::message>
