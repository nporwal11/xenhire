jQuery(document).ready(function ($) {
    let currentPage = 1;
    let pageSize = 10;
    let totalRecords = 0;
    let searchTimer = null;
    let filterStatus = -1; // Default: All
    let employersData = [];

    // Initial load
    loadEmployers();

    // Event Listeners
    $('#xh-search-input').on('input', function () {
        var val = $(this).val();
        if (val.length > 0) {
            $('#xh-clear-search-btn').show();
        } else {
            $('#xh-clear-search-btn').hide();
        }

        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentPage = 1;
            loadEmployers();
        }, 500);
    });

    // Clear Search Button
    $('#xh-clear-search-btn').click(function () {
        $('#xh-search-input').val('');
        $(this).hide();
        currentPage = 1;
        loadEmployers();
    });

    $('#xh-page-size').change(function () {
        pageSize = $(this).val();
        currentPage = 1;
        loadEmployers();
    });

    // Filter Dropdown Toggle
    $('#xh-filter-btn').click(function (e) {
        e.stopPropagation();
        $('#xh-filter-dropdown').toggle();
    });

    // Close dropdown when clicking outside
    $(window).click(function () {
        $('#xh-filter-dropdown').hide();
    });

    $('#xh-filter-dropdown').click(function (e) {
        e.stopPropagation();
    });

    // Apply Filter
    $('#xh-apply-filter').click(function () {
        filterStatus = $('#xh-filter-status').val();
        currentPage = 1;
        loadEmployers();
        $('#xh-filter-dropdown').hide();
    });

    function loadEmployers() {
        $('#xh-emp-list').html('<div class="xh-list-row"><div style="grid-column: 1/-1; text-align:center; padding: 20px;">Loading...</div></div>');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_list_employers',
                nonce: xenhireAjax.nonce,
                page_no: currentPage,
                page_size: pageSize,
                search: $('#xh-search-input').val(),
                is_active: filterStatus
            },
            success: function (res) {
                if (res.success) {
                    employersData = res.data.employers || [];
                    renderEmployers(res.data.employers);
                    updatePagination(res.data.metadata);
                } else {
                    $('#xh-emp-list').html('<div class="xh-list-row"><div style="grid-column: 1/-1; text-align:center; color:red; padding: 20px;">' + (res.data.message || 'Failed to load data') + '</div></div>');
                }
            },
            error: function () {
                $('#xh-emp-list').html('<div class="xh-list-row"><div style="grid-column: 1/-1; text-align:center; color:red; padding: 20px;">Server Error</div></div>');
            }
        });
    }

    function renderEmployers(employers) {
        if (!employers || employers.length === 0) {
            $('#xh-emp-list').html('<div class="xh-list-row"><div style="grid-column: 1/-1; text-align:center; padding: 20px;">No employers found.</div></div>');
            return;
        }

        let html = '';
        employers.forEach(emp => {
            let placeholderUrl = (typeof xenhireAjax !== 'undefined' && xenhireAjax.plugin_url ? xenhireAjax.plugin_url : '') + 'public/images/placeholder.png';
            let logoUrl = emp.LogoIMG || placeholderUrl;
            let logo = `<div class="employer-logo-wrapper"><img src="${logoUrl}" class="employer-logo" alt="${emp.CompanyName || 'Employer'}" onerror="this.src='${placeholderUrl}'"></div>`;
            let createdOn = emp.CreatedOn || '-';
            let isRecruiting = (emp.IsRecruiting == 1 || emp.IsActive == 1) ? 'checked' : '';
            let jobCount = emp.Requirements || 0;
            let empId = emp.EmployerID || emp.ID;

            html += `
            <div class="xh-list-row">
                <div>${logo}</div>
                <div class="xh-emp-brand">${emp.BrandName || '-'}</div>
                <div class="xh-emp-company">${emp.CompanyName || '-'}</div>
                <div class="xh-emp-date">${createdOn}</div>
                <div style="text-align: center;">
                    <label class="xh-toggle-switch">
                        <input type="checkbox" class="xh-emp-toggle" data-id="${empId}" ${isRecruiting}> 
                        <span class="xh-slider"></span>
                    </label>
                </div>
                <div style="text-align: center;">
                    <span class="xh-jobs-badge">${jobCount} Jobs</span>
                </div>
                <div style="text-align: center;">
                    <button class="xh-btn xh-secondary xh-btn-edit" data-id="${emp.ID}">Edit</button>
                </div>
            </div>`;
        });

        $('#xh-emp-list').html(html);

        // Hide "Add New Employer" button if we have employers (Limit to 1)
        if (employers.length > 0 || totalRecords > 0) {
            $('#xh-add-employer-btn').hide();
        } else {
            $('#xh-add-employer-btn').show();
        }

        // Bind Toggle Event
        $('.xh-emp-toggle').change(function () {
            let empId = $(this).data('id');
            let isActive = $(this).is(':checked') ? 1 : 0;
            toggleEmployerStatus(empId, isActive);
        });
    }

    function toggleEmployerStatus(empId, isActive) {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_toggle_employer_status',
                nonce: xenhireAjax.nonce,
                emp_id: empId,
                is_active: isActive
            },
            success: function (res) {
                if (!res.success) {
                    alert(res.data.message || 'Failed to update status');
                    // Revert toggle if failed
                    // logic to revert...
                }
            },
            error: function () {
                alert('Server error');
            }
        });
    }

    function updatePagination(meta) {
        totalRecords = meta.TotalRecordCount || 0;
        let totalPages = Math.ceil(totalRecords / pageSize);
        let start = (currentPage - 1) * pageSize + 1;
        let end = Math.min(currentPage * pageSize, totalRecords);

        if (totalRecords === 0) start = 0;

        $('#xh-page-info').text(`showing ${start} to ${end} of ${totalRecords} records`);

        let navHtml = '';
        navHtml += `<button class="xh-page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(1)"><i class="ki-duotone ki-double-left ki-fs-3"><span class="path1"></span><span class="path2"></span></i></button>`;
        navHtml += `<button class="xh-page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="ki-duotone ki-left ki-fs-3"></i></button>`;
        navHtml += `<button class="xh-page-btn active">${currentPage}</button>`;
        navHtml += `<button class="xh-page-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})"><i class="ki-duotone ki-right ki-fs-3"></i></button>`;
        navHtml += `<button class="xh-page-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="changePage(${totalPages})"><i class="ki-duotone ki-double-right ki-fs-3"><span class="path1"></span><span class="path2"></span></i></button>`;

        $('#xh-page-nav').html(navHtml);

        // Bind click events for dynamic buttons
        $('.xh-page-btn').not('.active, :disabled').click(function () {
            let onclick = $(this).attr('onclick');
            if (onclick) {
                let page = parseInt(onclick.match(/\d+/)[0]);
                if (page) {
                    currentPage = page;
                    loadEmployers();
                }
            }
        });
    }

    // Expose changePage globally if needed for inline onclicks (though we bind above)
    window.changePage = function (page) {
        currentPage = page;
        loadEmployers();
    };

    /* --- Add Employer Modal Logic --- */

    // Load Industry CBO
    var industryData = [];
    loadIndustryCBO();

    function loadIndustryCBO() {
        var input = $('#xh-industry-search');
        input.prop('disabled', true).val('Loading...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_cbo_items',
                nonce: xenhireAjax.nonce,
                key: 'Industry'
            },
            success: function (res) {
                if (res.success && res.data) {
                    industryData = [];
                    var items = res.data;

                    // Handle if data is a JSON string
                    if (typeof items === 'string') {
                        try {
                            items = JSON.parse(items);
                        } catch (e) {
                            console.error('Failed to parse Industry data string', e);
                            items = [];
                        }
                    }

                    // Handle wrapped arrays (e.g. { Options: [...] } or { Items: [...] })
                    if (!Array.isArray(items)) {
                        if (items.Options && Array.isArray(items.Options)) {
                            items = items.Options;
                        } else if (items.Items && Array.isArray(items.Items)) {
                            items = items.Items;
                        } else {
                            // Try to find any array property
                            for (var key in items) {
                                if (Array.isArray(items[key])) {
                                    items = items[key];
                                    break;
                                }
                            }
                        }
                    }

                    if (Array.isArray(items)) {
                        industryData = items.map(function (item) {
                            var value, text;
                            if (typeof item === 'string') {
                                value = item;
                                text = item;
                            } else {
                                value = item.Value || item.Key || item.ID || item.id;
                                text = item.DisplayText || item.Text || item.Name || item.Value || item.description;
                            }
                            return { label: text, value: value };
                        }).filter(function (item) {
                            return item.value != -1 && item.value != '-1';
                        });
                    }

                    input.prop('disabled', false).val('');

                    // Initialize Autocomplete
                    input.autocomplete({
                        source: industryData,
                        minLength: 0,
                        select: function (event, ui) {
                            event.preventDefault();
                            $('#xh-industry').val(ui.item.value);
                            $(this).val(ui.item.label);
                        },
                        focus: function (event, ui) {
                            event.preventDefault();
                            $(this).val(ui.item.label);
                        },
                        change: function (event, ui) {
                            if (!ui.item) {
                                $('#xh-industry').val($(this).val());
                            }
                        }
                    }).focus(function () {
                        $(this).autocomplete("search", "");
                    });

                } else {
                    console.error('Failed to load Industry CBO');
                    input.prop('disabled', false).val('').attr('placeholder', 'Failed to load industries');
                }
            },
            error: function () {
                console.error('Error loading Industry CBO');
                input.prop('disabled', false).val('').attr('placeholder', 'Error loading industries');
            }
        });
    }

    // CKEditor Initialization
    let descriptionEditor;
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#xh-description-editor'), {
                toolbar: ['bold', 'italic'],
                removePlugins: ['MediaEmbed']
            })
            .then(editor => {
                descriptionEditor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    }

    // Open Modal (Add New)
    // Open Modal (Add New)
    $('#xh-add-employer-btn').click(function () {
        $('#xh-add-employer-form')[0].reset();
        $('#xh-employer-id').val('-1');
        $('#xh-modal-title').text('Add New Employer');
        $('#xh-logo-preview').html('');
        $('#xh-logo-url').val('');
        if (descriptionEditor) {
            descriptionEditor.setData('');
        }
        $('#xh-industry').val(''); // Clear hidden input
        $('#xh-add-employer-modal').show();
    });

    // Open Modal (Edit)
    $(document).on('click', '.xh-btn-edit', function () {
        var empId = $(this).data('id');

        // Show loading state or clear previous data
        $('#xh-add-employer-form')[0].reset();
        $('#xh-employer-id').val(empId);
        $('#xh-modal-title').text('Loading...');
        $('#xh-add-employer-modal').show();

        // Disable inputs while loading
        $('#xh-add-employer-form input, #xh-add-employer-form textarea, #xh-add-employer-form button').prop('disabled', true);

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_employer_details',
                nonce: xenhireAjax.nonce,
                emp_id: empId
            },
            success: function (res) {
                if (res.success && res.data) {
                    var emp = res.data;

                    $('#xh-modal-title').text('Edit Employer');

                    $('#xh-brand-name').val(emp.BrandName);
                    $('#xh-company-name').val(emp.CompanyName);
                    $('#xh-website').val(emp.Website);

                    // Handle Industry
                    $('#xh-industry-search').val(emp.Industry);
                    $('#xh-industry').val(emp.Industry);

                    // Handle Description
                    let desc = emp.Description || '';
                    try {
                        desc = decodeURIComponent(desc);
                    } catch (e) {
                        console.warn('Failed to decode description:', e);
                    }

                    if (descriptionEditor) {
                        descriptionEditor.setData(desc);
                    } else {
                        $('#xh-description-editor').val(desc);
                    }

                    // Handle Logo
                    $('#xh-logo-url').val(emp.LogoIMG);
                    if (emp.LogoIMG) {
                        $('#xh-logo-preview').html(`<img src="${emp.LogoIMG}" style="max-height:100px;">`);
                    } else {
                        $('#xh-logo-preview').html('');
                    }

                    // Handle Recruiting Toggle
                    $('#xh-is-recruiting').prop('checked', emp.IsActive == 1);
                } else {
                    Swal.fire('Error', res.data.message || 'Failed to load employer details', 'error');
                    $('#xh-add-employer-modal').hide();
                }
            },
            error: function () {
                Swal.fire('Error', 'Server error loading details', 'error');
                $('#xh-add-employer-modal').hide();
            },
            complete: function () {
                // Re-enable inputs
                $('#xh-add-employer-form input, #xh-add-employer-form textarea, #xh-add-employer-form button').prop('disabled', false);
            }
        });
    });

    // Close Modal
    $('.xh-close-modal, #xh-cancel-btn').click(function () {
        $('#xh-add-employer-modal').hide();
    });

    // Close on click outside
    $(window).click(function (e) {
        if ($(e.target).is('#xh-add-employer-modal')) {
            $('#xh-add-employer-modal').hide();
        }
    });

    // Image Upload
    $('#xh-upload-logo-btn').click(function (e) {
        e.preventDefault();

        let image_frame;
        if (image_frame) {
            image_frame.open();
            return;
        }

        image_frame = wp.media({
            title: 'Select or Upload Logo',
            library: { type: 'image' },
            button: { text: 'Use this logo' },
            multiple: false
        });

        image_frame.on('select', function () {
            let selection = image_frame.state().get('selection').first().toJSON();
            $('#xh-logo-url').val(selection.url);
            $('#xh-logo-preview').html(`<img src="${selection.url}" style="max-height:100px;">`);
        });

        image_frame.open();
    });

    // Form Submission
    $('#xh-add-employer-form').submit(function (e) {
        e.preventDefault();

        // Get Description from CKEditor
        if (descriptionEditor) {
            $('#xh-description-editor').val(descriptionEditor.getData());
        }

        let formData = new FormData(this);
        formData.append('action', 'xenhire_save_employer');
        formData.append('nonce', xenhireAjax.nonce);

        // Handle checkbox manually if needed (though FormData usually handles it)
        let isRecruiting = $('#xh-is-recruiting').is(':checked') ? 1 : 0;
        formData.set('IsRecruiting', isRecruiting);

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Employer Saved',
                        text: '',
                        icon: 'success',
                        confirmButtonText: 'Okay'
                    }).then((result) => {
                        $('#xh-add-employer-modal').hide();
                        loadEmployers(); // Reload list
                    });
                } else {
                    Swal.fire('Error', res.data.message || 'Failed to save employer', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Server error occurred', 'error');
            }
        });
    });
});
