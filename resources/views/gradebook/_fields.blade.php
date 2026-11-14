<div class="form-grid">
    <x-input name="value" label="Ocena (1–6)" type="number" :value="$grade->value" required min="1" max="6" step="1" />
    <x-input name="weight" label="Waga (1–5)" type="number" :value="$grade->weight" required min="1" max="5" step="1" />
    <div class="wide">
        <x-select name="category" label="Kategoria" :options="array_combine(config('dziennik.grade_categories'), config('dziennik.grade_categories'))"
            :value="$grade->category" placeholder="Wybierz kategorię" required />
    </div>
    <div class="wide">
        <x-input name="description" label="Opis" :value="$grade->description" maxlength="255" placeholder="np. Funkcje liniowe" />
    </div>
</div>
