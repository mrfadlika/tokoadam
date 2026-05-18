/**
 * Toko Adam - Client-side Table Sort Engine
 */
document.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('table[data-sortable]');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('thead th[data-sort-key]');
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        // Exclude empty state rows from being sorted
        const isEmptyState = tbody.querySelector('.empty-state');
        if (isEmptyState) return;

        const originalRows = Array.from(tbody.querySelectorAll('tr'));
        let currentSortKey = null;
        let currentSortDirection = null; // 'asc', 'desc', or null
        
        headers.forEach(header => {
            header.classList.add('sortable-header');
            
            // Create sort indicator elements
            const indicator = document.createElement('span');
            indicator.className = 'sort-indicator';
            
            // SVG-based arrows for premium look matching the system style
            indicator.innerHTML = `
                <svg class="arrow arrow-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="19" x2="12" y2="5"></line>
                    <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
                <svg class="arrow arrow-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <polyline points="19 12 12 19 5 12"></polyline>
                </svg>
            `;
            header.appendChild(indicator);
            
            header.addEventListener('click', () => {
                const key = header.getAttribute('data-sort-key');
                let direction = 'asc';
                
                if (currentSortKey === key) {
                    if (currentSortDirection === 'asc') {
                        direction = 'desc';
                    } else if (currentSortDirection === 'desc') {
                        direction = null; // Reset to original database order
                    }
                }
                
                // Clear active classes from all headers
                headers.forEach(h => {
                    h.classList.remove('sort-active', 'sort-asc', 'sort-desc');
                });
                
                if (direction === null) {
                    // Restore original row sequence
                    tbody.innerHTML = '';
                    originalRows.forEach(row => tbody.appendChild(row));
                    currentSortKey = null;
                    currentSortDirection = null;
                    return;
                }
                
                header.classList.add('sort-active', direction === 'asc' ? 'sort-asc' : 'sort-desc');
                currentSortKey = key;
                currentSortDirection = direction;
                
                const columnIndex = Array.from(header.parentNode.children).indexOf(header);
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.sort((rowA, rowB) => {
                    const cellA = rowA.children[columnIndex];
                    const cellB = rowB.children[columnIndex];
                    
                    if (!cellA || !cellB) return 0;
                    
                    let valA = cellA.getAttribute('data-sort-value') !== null 
                        ? cellA.getAttribute('data-sort-value') 
                        : cellA.textContent.trim();
                        
                    let valB = cellB.getAttribute('data-sort-value') !== null 
                        ? cellB.getAttribute('data-sort-value') 
                        : cellB.textContent.trim();
                        
                    // Clean values for numeric parsing if they look numeric (remove spaces, dots, currency suffix, etc.)
                    const cleanValA = valA.replace(/[^0-9,\-]/g, '').replace(',', '.');
                    const cleanValB = valB.replace(/[^0-9,\-]/g, '').replace(',', '.');
                    
                    const numA = parseFloat(cleanValA);
                    const numB = parseFloat(cleanValB);
                    
                    const isNumA = !isNaN(numA) && isFinite(cleanValA) && cleanValA !== '';
                    const isNumB = !isNaN(numB) && isFinite(cleanValB) && cleanValB !== '';
                    
                    if (isNumA && isNumB) {
                        return direction === 'asc' ? numA - numB : numB - numA;
                    }
                    
                    // Fallback to normal string localeCompare sorting (e.g. for text)
                    return direction === 'asc' 
                        ? valA.localeCompare(valB, undefined, {numeric: true, sensitivity: 'base'})
                        : valB.localeCompare(valA, undefined, {numeric: true, sensitivity: 'base'});
                });
                
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
});
