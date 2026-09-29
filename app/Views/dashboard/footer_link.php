<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/js/vendor.bundle.base.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/chart.js/chart.umd.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/progressbar.js/progressbar.min.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/off-canvas.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/datatables.net/jquery.dataTables.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/template.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/settings.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/hoverable-collapse.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/todolist.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/jquery.cookie.js?v=' . time()); ?>" type="text/javascript"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/dashboard.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/datatable.js?v=' . time()); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/progressbar.js/1.0.1/progressbar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/script.js?v=' . time()); ?>"></script>
<script src="<?= base_url(env('ImagePath').'assets/js/mobile-table.js?v=' . time()); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- 404 Detection and Redirect Script -->
<script>
(function() {
    'use strict';
    
    // Function to check if current page is a 404 error
    function is404Page() {
        // Check multiple indicators of 404 page
        const pageTitle = document.title.toLowerCase();
        const bodyText = document.body ? document.body.innerText.toLowerCase() : '';
        const url = window.location.href;
        
        // Common 404 indicators (including CodeIgniter's specific message)
        const indicators = [
            '404',
            'page not found',
            'not found',
            'error 404',
            'file not found',
            'sorry! cannot seem to find',  // CodeIgniter's 404 message
            'cannot seem to find the page',
            'sorry cannot find'
        ];
        
        // Check title
        for (let indicator of indicators) {
            if (pageTitle.includes(indicator)) {
                return true;
            }
        }
        
        // Check body content (first 1000 chars to catch the message)
        const bodyPreview = bodyText.substring(0, 1000);
        for (let indicator of indicators) {
            if (bodyPreview.includes(indicator)) {
                return true;
            }
        }
        
        // Check for the specific 404 page structure (h1 with "404" and specific message)
        const h1Elements = document.querySelectorAll('h1');
        for (let h1 of h1Elements) {
            if (h1.textContent.trim() === '404') {
                // Found 404 heading, check if it's the error page
                const wrapDiv = h1.closest('.wrap');
                if (wrapDiv) {
                    const pElement = wrapDiv.querySelector('p');
                    if (pElement && pElement.textContent.toLowerCase().includes('cannot seem to find')) {
                        return true;
                    }
                }
                // If we see a standalone 404 heading, it's likely a 404 page
                return true;
            }
        }
        
        // Check if page has very minimal content (common for 404 pages)
        if (document.body && document.body.children.length < 3 && bodyText.length < 200) {
            // Check if it contains "404" anywhere
            if (bodyText.includes('404')) {
                return true;
            }
        }
        
        return false;
    }
    
    // Function to redirect to dashboard
    function redirectToDashboard() {
        const dashboardUrl = '<?= base_url("/dashboard") ?>';
        console.log('🔄 404 detected, redirecting to dashboard:', dashboardUrl);
        
        // Notify service worker if available
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'PAGE_404',
                url: window.location.href
            }).catch(() => {
                // Service worker message failed, continue with redirect
            });
        }
        
        // Redirect to dashboard
        window.location.href = dashboardUrl;
    }
    
    // Check for 404 when page loads (immediate check)
    function checkAndRedirect() {
        if (is404Page()) {
            redirectToDashboard();
            return true;
        }
        return false;
    }
    
    // Immediate check (for pages already loaded)
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // Check immediately
        if (!checkAndRedirect()) {
            // Also check after a short delay in case content loads asynchronously
            setTimeout(checkAndRedirect, 100);
            setTimeout(checkAndRedirect, 500);
            setTimeout(checkAndRedirect, 1000);
        }
    } else {
        // Wait for DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            if (!checkAndRedirect()) {
                // Multiple checks to catch async-loaded 404 pages
                setTimeout(checkAndRedirect, 100);
                setTimeout(checkAndRedirect, 500);
                setTimeout(checkAndRedirect, 1000);
            }
        });
    }
    
    // Listen for messages from service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'CHECK_404') {
                // Service worker is asking us to check for 404
                setTimeout(function() {
                    if (is404Page()) {
                        redirectToDashboard();
                    }
                }, 500);
            } else if (event.data && event.data.type === 'REDIRECT') {
                // Service worker is telling us to redirect
                if (event.data.url) {
                    window.location.href = event.data.url;
                }
            }
        });
    }
    
    // Also check on navigation (for SPA-like behavior)
    let lastUrl = location.href;
    new MutationObserver(function() {
        const currentUrl = location.href;
        if (currentUrl !== lastUrl) {
            lastUrl = currentUrl;
            setTimeout(function() {
                if (is404Page()) {
                    redirectToDashboard();
                }
            }, 500);
        }
    }).observe(document, { subtree: true, childList: true });
})();
</script>

<!-- <script src="https://cdn.jsdelivr.net/npm/progressbar.js/dist/progressbar.min.js"></script> -->

<!-- Global AJAX Security Setup -->
<script>
$(document).ready(function() {
    // Global AJAX setup to include CSRF token in all requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle CSRF token refresh from server responses (for multi-submission pages)
    $(document)
    .ajaxComplete(function(event, xhr, settings) {
        if (xhr.responseJSON && xhr.responseJSON.csrfHash) {
            $('meta[name="csrf-token"]').attr('content', xhr.responseJSON.csrfHash);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': xhr.responseJSON.csrfHash
                }
            });
        }
    })
    .ajaxError(function(event, xhr, settings) {
        if (xhr.status === 403) {
            console.warn('❌ Security token mismatch or expired (403). Refreshing page may be required.');
        }
    });
});
</script>

<!-- Session expiry handler: clear stale auth + redirect -->
<script>
(function() {
    'use strict';

    let isRedirecting = false;

    function isLoginPage() {
        var p = (window.location && window.location.pathname) ? window.location.pathname : '';
        return p === '/login' || p.endsWith('/login');
    }

    function getRequestUrl(resource) {
        try {
            if (!resource) return '';
            if (typeof resource === 'string') return resource;
            if (resource && typeof resource === 'object' && resource.url) return resource.url;
            return String(resource);
        } catch (e) {
            return '';
        }
    }

    function clearAuthAndRedirect(reason) {
        if (isRedirecting) return;
        isRedirecting = true;

        try { localStorage.removeItem('token'); } catch (e) {}
        try { sessionStorage.clear(); } catch (e) {}

        // Try to delete remember-me cookie (if it's not HttpOnly)
        try { document.cookie = 'remember_me_token=; Max-Age=0; path=/;'; } catch (e) {}

        // Clear CacheStorage to avoid stale responses
        try {
            if ('caches' in window) {
                caches.keys()
                    .then(function(keys) {
                        return Promise.all(keys.map(function(k) {
                            return caches.delete(k);
                        }));
                    })
                    .catch(function() {});
            }
        } catch (e) {}

        console.warn('Session expired; redirecting to login:', reason);
        window.location.replace('/login');
    }

    // Patch fetch globally to handle 401/403 from APIs
    try {
        if (window.fetch && !window.__sessionFetchPatched) {
            var originalFetch = window.fetch.bind(window);
            window.__sessionFetchPatched = true;

            window.fetch = async function(resource, options) {
                var response = await originalFetch(resource, options);
                var status = response ? response.status : 0;

                if ((status === 401 || status === 403) && !isLoginPage()) {
                    var url = getRequestUrl(resource) || '';

                    // Background notifications and push subscription APIs must NEVER trigger auth redirect
                    if (url.indexOf('/api/push') !== -1 || url.indexOf('/notifications') !== -1) {
                        return response;
                    }

                    var looksLikeApi = url.indexOf('/api/') !== -1 || url.indexOf('/auth') !== -1 || url.indexOf('/logout') !== -1;

                    // Only 401 indicates expired or invalid session token.
                    // 403 indicates permission denied for a specific action/role, not an expired session.
                    if (status === 401) {
                        clearAuthAndRedirect('fetch_http_' + status);
                    }
                }
                return response;
            };
        }
    } catch (e) {}

    // Patch jQuery AJAX to handle 401/403
    if (window.$ && $.ajaxSetup) {
        $(document).ajaxError(function(event, xhr) {
            var status = xhr && xhr.status ? xhr.status : 0;
            if ((status === 401 || status === 403) && !isLoginPage()) {
                var message = '';
                try {
                    if (xhr && xhr.responseJSON) {
                        message = String(xhr.responseJSON.message || xhr.responseJSON.error || '');
                    }
                } catch (e) {}

                var looksAuth = (status === 401) || /unauthorized|token|expired|invalid/i.test(message);
                if (looksAuth) {
                    clearAuthAndRedirect('xhr_http_' + status);
                }
            }
        });
    }
})();

// ─── Global Universal Table to Excel Exporter ───
window.exportTableToExcel = function (tableSelector, defaultName) {
    if (typeof XLSX === 'undefined') {
        Swal.fire('Error', 'Excel export library not loaded. Please refresh the page.', 'error');
        return;
    }

    var $table = $(tableSelector).first();
    if (!$table.length) {
        // Fallback: try finding first visible table in page
        $table = $('table:visible').first();
    }
    if (!$table.length) {
        Swal.fire('Notice', 'No table data found to export.', 'info');
        return;
    }

    var baseName = defaultName || 'Export_Data';
    var isDataTable = $.fn.DataTable && $.fn.DataTable.isDataTable($table);

    // Identify columns to exclude (Action, Details, checkboxes, buttons)
    var headers = [];
    var excludeColIdx = [];

    $table.find('thead tr').first().find('th, td').each(function (idx) {
        var text = $(this).text().trim();
        var lower = text.toLowerCase();
        // Ignore empty header, action/actions/details/options/expand/checkbox
        if (!text || lower === 'action' || lower === 'actions' || lower === 'details' || lower === 'option' || lower === 'options' || lower === '#' || $(this).hasClass('mobile-expand-col') || $(this).css('display') === 'none') {
            excludeColIdx.push(idx);
        } else {
            headers.push(text);
        }
    });

    var dataRows = [];
    dataRows.push(headers);

    var extractRowData = function (tr) {
        var rowVals = [];
        $(tr).find('td, th').each(function (idx) {
            if (excludeColIdx.indexOf(idx) !== -1) return;
            // Extract clean text, ignore buttons / modals / dropdown text
            var $cell = $(this).clone();
            $cell.find('button, .dropdown-menu, script, style, .expand-toggle, .mobile-expand-details').remove();
            var cellText = $cell.text().replace(/\s+/g, ' ').trim();
            rowVals.push(cellText);
        });
        if (rowVals.length > 0) {
            dataRows.push(rowVals);
        }
    };

    if (isDataTable) {
        var dt = $table.DataTable();
        // Get all matching filtered nodes across all pages
        dt.rows({ search: 'applied' }).nodes().each(function (node) {
            extractRowData(node);
        });
    } else {
        $table.find('tbody tr').each(function () {
            if ($(this).hasClass('expanded-details-row') || $(this).css('display') === 'none' && $(this).text().indexOf('No ') !== -1) return;
            extractRowData(this);
        });
    }

    if (dataRows.length <= 1) {
        Swal.fire('Notice', 'No records found to export.', 'info');
        return;
    }

    var ws = XLSX.utils.aoa_to_sheet(dataRows);

    // Auto-fit column widths
    var colWidths = [];
    dataRows.forEach(function (row) {
        row.forEach(function (val, cIdx) {
            var len = (val ? String(val).length : 10) + 3;
            colWidths[cIdx] = Math.max(colWidths[cIdx] || 12, len);
        });
    });
    ws['!cols'] = colWidths.map(function (w) { return { wch: Math.min(w, 50) }; });

    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');

    var dateStr = new Date().toISOString().slice(0, 10);
    var cleanFilename = baseName.replace(/[^a-zA-Z0-9_-]/g, '_') + '_' + dateStr + '.xlsx';

    XLSX.writeFile(wb, cleanFilename);

    Swal.fire({
        icon: 'success',
        title: 'Exported!',
        text: 'Table data exported to Excel successfully.',
        toast: true,
        position: 'top-end',
        timer: 3000,
        showConfirmButton: false
    });
};

// Global click handler for any .export-page-btn
$(document).on('click', '.export-page-btn', function (e) {
    e.preventDefault();
    var tableSel = $(this).data('table') || 'table';
    var filename = $(this).data('filename') || 'Export_Data';
    window.exportTableToExcel(tableSel, filename);
});

// Global Validation Error Handler
window.displayValidationErrors = function(errors, formSelector) {
    let form = formSelector ? $(formSelector) : $(document);
    $.each(errors, function(key, value) {
        let inputField = form.find(`[name="${key}"], [name="${key}[]"]`);
        if (inputField.length) {
            inputField.addClass('is-invalid');
            let target = inputField.closest('.input-group').length ? inputField.closest('.input-group') : inputField;
            target.next('.invalid-feedback').remove();
            target.after(`<div class="invalid-feedback d-block">${value}</div>`);
        }
    });
};
</script>