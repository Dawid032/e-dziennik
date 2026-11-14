{{-- Lista uwag ucznia, tylko do odczytu. Oczekuje $notes. --}}
<div class="card">
    @forelse ($notes as $note)
        <article class="note">
            <div class="note-meta">
                <span class="tag tag-{{ $note->type }}">{{ $note->type_name }}</span>
                <span class="muted">{{ $note->created_at->format('d.m.Y') }}, {{ $note->teacher->full_name }}</span>
            </div>
            <p>{{ $note->content }}</p>
        </article>
    @empty
        <p class="empty">Nie ma żadnych uwag.</p>
    @endforelse
</div>
