document.addEventListener('DOMContentLoaded', () => {

    const addIngBtn = document.getElementById('addIngBtn');
    const ingContainer = document.getElementById('ingContainer');

    if (addIngBtn && ingContainer) {
        addIngBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const row = document.createElement('div');
            row.className = 'ing-row';
            row.innerHTML = `
                <input type="text" name="ing_name[]" placeholder="Ingredient name" required>
                <input type="number" step="0.01" name="ing_qty[]" placeholder="Qty" required>
                <input type="text" name="ing_unit[]" placeholder="Unit">
                <button type="button" class="btn btn-danger remove-ing-btn">&times;</button>
            `;
            ingContainer.appendChild(row);
        });

        ingContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-ing-btn')) {
                if (ingContainer.children.length > 1) {
                    e.target.parentElement.remove();
                }
            }
        });
    }

    const favBtn = document.getElementById('favBtn');
    if (favBtn) {
        favBtn.addEventListener('click', async function() {
            const recipeId = this.dataset.id;

            try {
                const response = await fetch('favorite.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ recipe_id: recipeId })
                });

                const result = await response.json();

                if (result.status === 'added') {
                    this.innerText = 'Saved';
                    this.classList.add('btn-fav-active');
                } else if (result.status === 'removed') {
                    this.innerText = 'Save Favorite';
                    this.classList.remove('btn-fav-active');
                }
            } catch (err) {
                console.error('Error updating favorite:', err);
            }
        });
    }
});