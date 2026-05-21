/* Toko Adam - Main JS */
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (mobile)
    const menuBtn = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
    
    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Alert close button
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 200);
        });
    });
    
    // Format rupiah on input
    document.querySelectorAll('.input-rupiah').forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '');
            this.value = val;
        });
    });

    document.addEventListener('click', handleGlobalClick);

    // Create the modal element once
    createDetailModal();

    // Setup compact tables
    enhanceCompactTables();
    updateCompactTableModes();
    window.addEventListener('resize', updateCompactTableModes);
    window.addEventListener('load', updateCompactTableModes);
});

// Utility: Format number as rupiah string
function formatRupiah(num) {
    return 'Rp ' + parseInt(num || 0).toLocaleString('id-ID');
}

function handleGlobalClick(e) {
    const confirmEl = e.target.closest('[data-confirm]');
    if (confirmEl) {
        if (!confirm(confirmEl.dataset.confirm || 'Yakin ingin menghapus?')) {
            e.preventDefault();
            return;
        }
    }

    const toggleBtn = e.target.closest('.mobile-view-toggle');
    if (toggleBtn) {
        e.preventDefault();
        openDetailModal(toggleBtn);
    }
}

function normalizeTableLabel(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
}

/* ===== Detail Modal ===== */

function createDetailModal() {
    if (document.getElementById('detailModalOverlay')) {
        return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'detail-modal-overlay';
    overlay.id = 'detailModalOverlay';

    overlay.innerHTML = `
        <div class="detail-modal" id="detailModal">
            <div class="detail-modal-handle"></div>
            <div class="detail-modal-header">
                <h3 id="detailModalTitle">Detail</h3>
                <button type="button" class="detail-modal-close" id="detailModalClose">&times;</button>
            </div>
            <div class="detail-modal-body">
                <div class="detail-modal-grid" id="detailModalGrid"></div>
            </div>
            <div class="detail-modal-actions" id="detailModalActions"></div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeDetailModal();
        }
    });

    // Close button
    document.getElementById('detailModalClose').addEventListener('click', closeDetailModal);

    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-active')) {
            closeDetailModal();
        }
    });

    // Swipe down to close on mobile
    let startY = 0;
    let currentY = 0;
    const modal = document.getElementById('detailModal');

    modal.addEventListener('touchstart', function(e) {
        if (modal.scrollTop <= 0) {
            startY = e.touches[0].clientY;
        }
    }, { passive: true });

    modal.addEventListener('touchmove', function(e) {
        if (modal.scrollTop <= 0) {
            currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            if (diff > 0) {
                modal.style.transform = 'translateY(' + diff + 'px)';
            }
        }
    }, { passive: true });

    modal.addEventListener('touchend', function() {
        const diff = currentY - startY;
        if (diff > 100) {
            closeDetailModal();
        } else {
            modal.style.transform = '';
        }
        startY = 0;
        currentY = 0;
    });
}

function openDetailModal(toggleBtn) {
    const row = toggleBtn.closest('tr');
    if (!row) return;

    const table = row.closest('table');
    if (!table) return;

    const headerRow = table.querySelector('thead tr');
    if (!headerRow) return;

    const headers = Array.from(headerRow.children).filter(cell => cell.matches('th, td'));
    const cells = Array.from(row.cells);

    const grid = document.getElementById('detailModalGrid');
    const actionsContainer = document.getElementById('detailModalActions');
    const titleEl = document.getElementById('detailModalTitle');

    grid.innerHTML = '';
    actionsContainer.innerHTML = '';

    // Find first visible text to use as title
    let modalTitle = 'Detail';
    for (const cell of cells) {
        if (!cell.classList.contains('mobile-view-col') && !cell.classList.contains('mobile-hide-col')) {
            const text = normalizeTableLabel(cell.textContent);
            if (text && text.length > 2) {
                modalTitle = text.length > 40 ? text.substring(0, 40) + '…' : text;
                break;
            }
        }
    }
    titleEl.textContent = modalTitle;

    // Build detail items from ALL columns (including hidden ones)
    let hasActions = false;
    
    headers.forEach((header, index) => {
        if (header.classList.contains('mobile-view-col')) return;
        
        const cell = cells[index];
        if (!cell) return;

        const label = normalizeTableLabel(header.textContent);
        if (!label) return;

        // Check if this is an action column
        const isActionCol = header.textContent.toLowerCase().includes('aksi') ||
                            header.textContent.toLowerCase().includes('action');
        const hasButtons = cell.querySelector('.btn, .btn-group, a.btn');

        if (isActionCol && hasButtons) {
            hasActions = true;
            // Clone action buttons into the actions container
            const btns = cell.querySelectorAll('.btn, a.btn');
            btns.forEach(btn => {
                const clone = btn.cloneNode(true);
                actionsContainer.appendChild(clone);
            });
            return;
        }

        // Get cell content - prefer the raw content without mobile-only elements
        let valueHtml = '';
        const clonedCell = cell.cloneNode(true);
        // Remove mobile-only-inline from cloned cell since we show all data
        clonedCell.querySelectorAll('.mobile-only-inline').forEach(el => el.remove());
        valueHtml = clonedCell.innerHTML.trim();

        const valueText = normalizeTableLabel(clonedCell.textContent);
        const hasRichContent = Boolean(clonedCell.querySelector('a, img, .badge, .btn'));

        if (!hasRichContent && (!valueText || valueText === '-')) return;

        const item = document.createElement('div');
        item.className = 'detail-modal-item';

        const labelNode = document.createElement('div');
        labelNode.className = 'detail-modal-label';
        labelNode.textContent = label;

        const valueNode = document.createElement('div');
        valueNode.className = 'detail-modal-value';
        valueNode.innerHTML = valueHtml;

        item.appendChild(labelNode);
        item.appendChild(valueNode);
        grid.appendChild(item);
    });

    // Show/hide actions container
    actionsContainer.style.display = hasActions ? 'flex' : 'none';

    // Open modal
    const modalOverlay = document.getElementById('detailModalOverlay');
    const modal = document.getElementById('detailModal');
    
    // Reset transform
    modal.style.transform = '';
    
    // Show overlay first, then animate
    modalOverlay.style.display = 'flex';
    // Force reflow
    modalOverlay.offsetHeight;
    modalOverlay.classList.add('is-active');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    const modalOverlay = document.getElementById('detailModalOverlay');
    const modal = document.getElementById('detailModal');
    
    modalOverlay.classList.remove('is-active');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        modalOverlay.style.display = 'none';
        modal.style.transform = '';
    }, 300);
}

/* ===== Compact Tables ===== */

function enhanceCompactTables() {
    document.querySelectorAll('table.table-compact-mobile').forEach((table, tableIndex) => {
        if (table.dataset.compactReady === '1') {
            return;
        }

        const headerRow = table.querySelector('thead tr');
        if (!headerRow) {
            table.dataset.compactReady = '1';
            return;
        }

        const headers = Array.from(headerRow.children).filter(cell => cell.matches('th, td'));
        const hiddenIndexes = headers
            .map((header, index) => header.classList.contains('mobile-hide-col') ? index : -1)
            .filter(index => index >= 0);

        if (!hiddenIndexes.length) {
            table.dataset.compactReady = '1';
            return;
        }

        // Add "View" column header
        const viewHeader = document.createElement('th');
        viewHeader.className = 'mobile-view-col';
        viewHeader.textContent = '';
        headerRow.appendChild(viewHeader);

        // Add "View" button to each data row
        Array.from(table.tBodies).forEach((tbody) => {
            Array.from(tbody.rows).forEach((row) => {
                if (row.classList.contains('mobile-detail-row')) {
                    return;
                }

                const cells = Array.from(row.cells);
                if (isEmptyTableRow(row, cells)) {
                    return;
                }

                row.classList.add('mobile-summary-row');

                const toggleCell = document.createElement('td');
                toggleCell.className = 'mobile-view-col text-center';
                toggleCell.innerHTML = '<button type="button" class="btn btn-sm btn-outline mobile-view-toggle" aria-label="Lihat detail">Detail</button>';
                row.appendChild(toggleCell);
            });
        });

        table.dataset.compactReady = '1';
    });
}

function isEmptyTableRow(row, cells) {
    if (!cells.length) {
        return true;
    }

    if (row.querySelector('.empty-state')) {
        return true;
    }

    return cells.length === 1 && Number(cells[0].getAttribute('colspan') || 1) > 1;
}

function updateCompactTableMode(table) {
    const wrapper = table.closest('.table-responsive');
    if (!wrapper) {
        return;
    }

    wrapper.classList.remove('compact-mode');
    const shouldCompact = window.innerWidth <= 1024;
    wrapper.classList.toggle('compact-mode', shouldCompact);
}

function updateCompactTableModes() {
    document.querySelectorAll('table.table-compact-mobile').forEach(table => {
        updateCompactTableMode(table);
    });
}
