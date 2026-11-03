<script>
    (function () {
        function getAllSidebarGroupLabels() {
            return Array.from(document.querySelectorAll('.fi-sidebar-group[data-group-label]'))
                .map((el) => el.getAttribute('data-group-label'))
                .filter((label) => Boolean(label));
        }

        function syncSidebarAccordionOnLoad() {
            const store = window.Alpine && window.Alpine.store ? window.Alpine.store('sidebar') : null;
            if (!store) {
                return;
            }

            const allLabels = getAllSidebarGroupLabels();
            if (allLabels.length === 0) {
                return;
            }

            // Cari grup menu yang sedang aktif berdasarkan halaman yang dibuka
            const activeGroupEl = document.querySelector('.fi-sidebar-group.fi-active[data-group-label]');
            const activeLabel = activeGroupEl
                ? activeGroupEl.getAttribute('data-group-label')
                : 'Keuangan & SPJ';

            const collapsed = allLabels.filter((label) => label !== activeLabel);
            store.collapsedGroups = collapsed;
            localStorage.setItem('collapsedGroups', JSON.stringify(collapsed));
        }

        function patchSidebarStoreAccordion() {
            const store = window.Alpine && window.Alpine.store ? window.Alpine.store('sidebar') : null;
            if (!store || store.__accordionPatched) {
                return;
            }

            store.__accordionPatched = true;

            store.toggleCollapsedGroup = function (group) {
                const allLabels = getAllSidebarGroupLabels();
                const isCurrentlyCollapsed = this.groupIsCollapsed(group);

                if (isCurrentlyCollapsed) {
                    // Saat membuka 1 grup menu, otomatis tutup seluruh grup menu lainnya
                    this.collapsedGroups = allLabels.filter((label) => label !== group);
                } else {
                    // Saat menutup grup yang sedang terbuka
                    this.collapsedGroups = Array.from(new Set([...(this.collapsedGroups || []), group]));
                }

                localStorage.setItem('collapsedGroups', JSON.stringify(this.collapsedGroups));
            };

            syncSidebarAccordionOnLoad();
        }

        document.addEventListener('alpine:initialized', patchSidebarStoreAccordion);
        document.addEventListener('DOMContentLoaded', () => setTimeout(patchSidebarStoreAccordion, 50));
        document.addEventListener('livewire:navigated', () => {
            patchSidebarStoreAccordion();
            syncSidebarAccordionOnLoad();
        });
    })();
</script>
