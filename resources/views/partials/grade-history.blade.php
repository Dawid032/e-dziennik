{{-- Tabela historii zmian ocen. Oczekuje $history i opcjonalnie $showStudent. --}}
@php($showStudent = $showStudent ?? false)

<div class="card table-wrap">
    <table>
        <thead>
            <tr>
                <th>Data</th>
                @if ($showStudent)
                    <th>Uczeń</th>
                @endif
                <th>Przedmiot</th>
                <th>Akcja</th>
                <th>Było</th>
                <th>Jest</th>
                <th>Autor zmiany</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($history as $entry)
                <tr>
                    <td class="nowrap">{{ $entry->created_at->format('d.m.Y H:i') }}</td>
                    @if ($showStudent)
                        <td>{{ $entry->grade->student->full_name }}</td>
                    @endif
                    <td>{{ $entry->grade->subject->name }}</td>
                    <td><span class="tag tag-{{ $entry->action }}">{{ $entry->action_name }}</span></td>
                    <td>{{ $entry->before }}</td>
                    <td>{{ $entry->after }}</td>
                    <td>{{ $entry->author->full_name }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ $showStudent ? 7 : 6 }}">Brak zmian do wyświetlenia.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
