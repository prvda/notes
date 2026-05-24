document.addEventListener('alpine:init', () => {
    Alpine.data('noteCard', (noteId, initialPinned, csrfToken) => ({
        pinned: initialPinned,
        loading: false,
        error: '',

        async togglePin() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch(`/notes/${noteId}/toggle-pin`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new URLSearchParams({ _token: csrfToken }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const payload = await response.json();
                this.pinned = Boolean(payload.is_pinned);
                this.$nextTick(() => this.moveCard());
            } catch (error) {
                this.error = 'Не удалось обновить закрепление.';
            } finally {
                this.loading = false;
            }
        },

        moveCard() {
            const grid = this.$root.closest('.notes-grid');

            if (!grid) {
                return;
            }

            if (this.pinned) {
                grid.prepend(this.$root);
                return;
            }

            const cards = Array.from(grid.querySelectorAll('.note-card'));
            const lastPinned = cards.reverse().find((card) => card !== this.$root && card.dataset.pinned === '1');

            if (lastPinned) {
                lastPinned.after(this.$root);
                return;
            }

            grid.append(this.$root);
        },
    }));
});

