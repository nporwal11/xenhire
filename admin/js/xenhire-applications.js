jQuery(document).ready(function ($) {
    let currentPage = 1;
    let pageSize = 10;
    let totalRecords = 0;

    function loadApplications(showLoading) {
        if (showLoading !== false) {
            $('#xh-app-list').html('<div class="xh-loading" style="text-align: center;">Loading applications...</div>');
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'xenhire_list_applications',
                nonce: xenhireAjax.nonce,
                page_no: currentPage,
                page_size: pageSize,
                job_id: -1 // Default to all jobs
            },
            success: function (res) {
                if (res.success) {
                    renderApplications(res.data.applications);
                    updatePagination(res.data.metadata);
                } else {
                    $('#xh-app-list').html('<div class="xh-error" style="text-align: center;">' + (res.data.message || 'Failed to load data') + '</div>');
                }
            },
            error: function () {
                $('#xh-app-list').html('<div class="xh-error" style="text-align: center;">Server Error</div>');
            }
        });
    }

    function renderApplications(apps) {
        if (!apps || apps.length === 0) {
            $('#xh-app-list').html('<div class="xh-empty" style="text-align: center;">No applications found.</div>');
            return;
        }

        let html = '';
        apps.forEach(app => {
            // Map API fields to UI
            let jobTitle = app.JobTitle || '-';
            let candidateName = app.Candidate || '-';
            let rating = app.Rating || 0;
            let stage = app.Stage || 'New';
            let stageColor = app.StageColor || '#111827';
            let date = app.CreatedOn || '-';
            let time = app.CreatedTime || '';
            let empRole = app.Designation || '-';
            let empCompany = app.Employer || '-';
            let exp = app.ExpInYears || '-';
            let salary = app.CurrentSalary || '-';
            let avatar = app.PhotoIMG || xenhireAjax.plugin_url + 'public/images/placeholder.png';

            // Interview Status comes as HTML, strip tags for our badge style
            let interviewHtml = app.InterviewStatus || 'Not Attempted';
            let interviewText = interviewHtml.replace(/<[^>]*>?/gm, '');
            let interviewBadgeClass = (interviewText.trim().toLowerCase() === 'completed') ? 'xh-badge-light-green' : 'xh-badge-light-red';

            let aiScore = app.AIScore;
            let aiScoreHtml = '';
            if (aiScore !== undefined && aiScore !== null && aiScore !== '' && aiScore != 0) {
                let starRating = Math.round(parseFloat(aiScore));
                aiScoreHtml = `<div class="xh-rating" style="justify-content: flex-start; margin-top: 4px;">${renderStars(starRating)}</div>`;
            }

            html += `
            <div class="xh-list-row" id="xh-app-row-${app.ID}">
                <div class="xh-col xh-col-name">
                    <img src="${avatar}" class="xh-avatar" alt="">
                    <div class="xh-name-info">
                        <div class="xh-name-link">${jobTitle}</div>
                        <div class="xh-sub-text">${candidateName}</div>
                        <div class="xh-rating">
                            ${renderStars(rating)}
                        </div>
                    </div>
                </div>
                <div class="xh-col xh-col-stage">
                    <span class="xh-badge" style="background-color: ${stageColor}; color: #fff;">${stage}</span>
                </div>
                <div class="xh-col xh-col-date">
                    <div class="xh-main-text">${date}</div>
                    <div class="xh-sub-text">${time}</div>
                </div>
                <div class="xh-col xh-col-emp">
                    <div class="xh-main-text">${empRole}</div>
                    <div class="xh-sub-text">${empCompany}</div>
                </div>
                <div class="xh-col xh-col-exp">
                    <div class="xh-main-text">${exp}</div>
                    <div class="xh-main-text">${salary}</div>
                </div>
                <div class="xh-col xh-col-int">
                    <span class="xh-badge ${interviewBadgeClass}">${interviewText}</span>
                    <div title="Overall AI Score">${aiScoreHtml}</div>
                </div>
                <div class="xh-col xh-col-action">
                    <button class="xh-btn xh-secondary" onclick="window.location.href='admin.php?page=xenhire-candidate-details&id=${app.ID}'">View</button>
                    <button class="xh-btn xh-danger xh-btn-delete" data-id="${app.ID}"><span class="fa fa-trash-alt"></span></button>
                </div>
            </div>`;
        });

        $('#xh-app-list').html(html);
    }

    function renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            let activeClass = i <= rating ? 'active' : '';
            stars += `<i class="ki-duotone ki-star ${activeClass}">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>`;
        }
        return stars;
    }

    function updatePagination(meta) {
        totalRecords = meta.TotalRecordCount || 0;
        let totalPages = Math.ceil(totalRecords / pageSize);
        let start = (currentPage - 1) * pageSize + 1;
        let end = Math.min(currentPage * pageSize, totalRecords);

        if (totalRecords === 0) start = 0;

        $('#xh-page-info').text(`showing ${start} to ${end} of ${totalRecords} records`);

        let navHtml = '';

        // First & Prev
        navHtml += `<button class="xh-page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(1)"><i class="ki-duotone ki-double-left ki-fs-3"><span class="path1"></span><span class="path2"></span></i></button>`;
        navHtml += `<button class="xh-page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="ki-duotone ki-left ki-fs-3"></i></button>`;

        // Smart Pagination: Max 5 pages
        let maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            navHtml += `<button class="xh-page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        }

        // Next & Last
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
                    loadApplications();
                }
            }
        });
    }

    // Event Listeners
    $('#xh-page-size').change(function () {
        pageSize = parseInt($(this).val());
        currentPage = 1;
        loadApplications();
    });

    $('.xh-btn-icon[title="Refresh"]').click(function () {
        loadApplications();
    });

    // Delete Handler
    $(document).on('click', '.xh-btn-delete', function () {
        let deleteId = $(this).data('id');
        if (!deleteId) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "The application along with its interview will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-light'
            },
            cancelButtonColor: '#17c653',
        }).then((result) => {
            if (result.isConfirmed) {
                let $row = $('#xh-app-row-' + deleteId);

                // Optimistic UI: Hide immediately
                $row.hide();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'xenhire_delete_application',
                        nonce: xenhireAjax.nonce,
                        id: deleteId
                    },
                    success: function (res) {
                        if (res.success) {
                            $row.remove(); // Permanent remove
                            if ($('.xh-list-row').length === 0) {
                                loadApplications(false);
                            }
                        } else {
                            // Revert on error
                            $row.show();
                            Swal.fire(
                                'Error!',
                                (res.data.message || 'Failed to delete'),
                                'error'
                            );
                        }
                    },
                    error: function () {
                        // Revert on error
                        $row.show();
                        Swal.fire(
                            'Error!',
                            'Server Error',
                            'error'
                        );
                    }
                });
            }
        });
    });



    // --- CBO Loading ---
    function loadCBO(key, selector) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'xenhire_get_cbo_items',
                nonce: xenhireAjax.nonce,
                key: key
            },
            success: function (res) {
                if (res.success && res.data) {
                    let items = res.data;

                    // Normalize data structure
                    if (typeof items === 'string') {
                        try { items = JSON.parse(items); } catch (e) { items = []; }
                    }
                    if (!Array.isArray(items)) {
                        if (items.Options && Array.isArray(items.Options)) items = items.Options;
                        else if (items.Items && Array.isArray(items.Items)) items = items.Items;
                        else {
                            // Fallback
                            let keys = Object.keys(items);
                            for (let i = 0; i < keys.length; i++) {
                                if (Array.isArray(items[keys[i]])) { items = items[keys[i]]; break; }
                            }
                        }
                    }

                    if (Array.isArray(items)) {
                        let $select = $(selector);
                        // Keep first option (All ...)
                        let firstOption = $select.find('option:first');
                        $select.empty().append(firstOption);

                        // For custom dropdown
                        let isCustom = selector === '#filter-requirement';
                        let $customList = isCustom ? $('#custom-req-select .xh-options-list') : null;
                        if (isCustom) $customList.empty().append('<div class="xh-option selected" data-value="-1">All Jobs</div>');

                        items.forEach(item => {
                            let value, text;
                            if (typeof item === 'string') { value = item; text = item; }
                            else if (typeof item === 'object') {
                                value = item.Value || item.Key || item.ID || item.id;
                                text = item.DisplayText || item.Text || item.Name || item.Value || item.description;
                            }
                            if (value && text && value != -1) {
                                $select.append(`<option value="${value}">${text}</option>`);
                                if (isCustom) {
                                    $customList.append(`<div class="xh-option" data-value="${value}">${text}</div>`);
                                }
                            }
                        });
                    }
                }
            }
        });
    }

    // Load CBOs
    loadCBO('Requirement', '#filter-requirement');
    loadCBO('VendorUser', '#filter-vendor');
    loadCBO('Rating', '#filter-rating');
    loadCBO('Rating', '#filter-ai-score'); // Reusing Rating CBO for AI Score
    loadCBO('Stage', '#filter-stage');

    // --- Custom Select Logic (Generic) ---
    $('.xh-custom-select').each(function () {
        const $select = $(this);
        const $trigger = $select.find('.xh-select-trigger');
        const $searchInput = $select.find('.xh-select-search');
        const $hiddenSelect = $select.find('select');

        $trigger.click(function (e) {
            e.stopPropagation();
            // Close other dropdowns
            $('.xh-custom-select').not($select).removeClass('open');

            $select.toggleClass('open');
            if ($select.hasClass('open') && $searchInput.length) {
                $searchInput.focus();
            }
        });

        $select.on('click', '.xh-option', function () {
            let val = $(this).data('value');
            let text = $(this).text();

            $select.find('.xh-selected-text').text(text);
            $select.find('.xh-option').removeClass('selected');
            $(this).addClass('selected');

            $hiddenSelect.val(val).trigger('change');
            $select.removeClass('open');
        });

        if ($searchInput.length) {
            $searchInput.on('input', function () {
                let term = $(this).val().toLowerCase();
                $select.find('.xh-option').each(function () {
                    let text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(term) > -1);
                });
            });
        }
    });

    $(window).click(function () {
        $('.xh-custom-select').removeClass('open');
    });


    // --- Download CSV ---
    $('#xh-btn-download').click(function () {
        let reqId = $('#filter-requirement').val();
        let interviewStatus = $('#filter-interview-status').val();
        let ratingId = $('#filter-rating').val();
        let aiScore = $('#filter-ai-score').val();
        let stageId = $('#filter-stage').val();

        let name = $('#filter-name').val();
        let email = $('#filter-email').val();
        let mobile = $('#filter-mobile').val();
        let expFrom = $('#filter-exp-from').val();
        let expTo = $('#filter-exp-to').val();

        // Create a hidden form and submit it
        let form = $('<form>', {
            action: ajaxurl,
            method: 'POST',
            target: '_blank' // Optional: open in new tab to avoid page reload issues
        });

        form.append($('<input>', { type: 'hidden', name: 'action', value: 'xenhire_download_applications_csv' }));
        form.append($('<input>', { type: 'hidden', name: 'nonce', value: xenhireAjax.nonce }));
        form.append($('<input>', { type: 'hidden', name: 'job_id', value: reqId }));
        form.append($('<input>', { type: 'hidden', name: 'rating_id', value: ratingId }));
        form.append($('<input>', { type: 'hidden', name: 'stage_id', value: stageId }));

        form.append($('<input>', { type: 'hidden', name: 'interview_status', value: interviewStatus }));
        form.append($('<input>', { type: 'hidden', name: 'ai_score', value: aiScore }));
        form.append($('<input>', { type: 'hidden', name: 'search', value: name }));
        form.append($('<input>', { type: 'hidden', name: 'email', value: email }));
        form.append($('<input>', { type: 'hidden', name: 'mobile', value: mobile }));
        form.append($('<input>', { type: 'hidden', name: 'exp_from', value: expFrom }));
        form.append($('<input>', { type: 'hidden', name: 'exp_to', value: expTo }));

        $('body').append(form);
        form.submit();
        form.remove();
    });

    // --- Filter Logic ---
    $('#xh-toggle-filter').click(function (e) {
        e.stopPropagation();
        $('#xh-filter-panel').toggle();
    });

    $('#xh-filter-panel').click(function (e) {
        e.stopPropagation();
    });

    $(window).click(function () {
        $('.xh-custom-select').removeClass('open');
        $('#xh-filter-panel').hide();
    });

    $('#xh-toggle-additional').click(function () {
        $(this).toggleClass('open');
        $('#xh-additional-filters').slideToggle();
    });

    $('#xh-apply-filter').click(function () {
        currentPage = 1;
        loadApplications();
    });

    $('#xh-clear-filter').click(function () {
        $('#filter-interview-status').val('-1');
        $('#filter-rating').val('-1');
        $('#filter-ai-score').val('-1');
        $('#filter-stage').val('-1');
        $('#filter-name').val('');
        $('#filter-email').val('');
        $('#filter-mobile').val('');
        $('#filter-exp-from').val('');
        $('#filter-exp-to').val('');
        currentPage = 1;
        loadApplications();
    });

    $('#filter-requirement').change(function () {
        currentPage = 1;
        loadApplications();
    });

    // Update loadApplications to use filters
    function loadApplications() {
        $('#xh-app-list').html('<div class="xh-loading" style="text-align: center;">Loading applications...</div>');

        // Collect filters
        let reqId = $('#filter-requirement').val();
        let interviewStatus = $('#filter-interview-status').val();
        let ratingId = $('#filter-rating').val();
        let aiScore = $('#filter-ai-score').val();
        let stageId = $('#filter-stage').val();

        let name = $('#filter-name').val();
        let email = $('#filter-email').val();
        let mobile = $('#filter-mobile').val();
        let expFrom = $('#filter-exp-from').val();
        let expTo = $('#filter-exp-to').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'xenhire_list_applications',
                nonce: xenhireAjax.nonce,
                page_no: currentPage,
                page_size: pageSize,
                job_id: reqId,
                rating_id: ratingId,
                stage_id: stageId,
                // New params
                interview_status: interviewStatus,
                ai_score: aiScore,
                search: name, // Mapping Name to Search
                email: email,
                mobile: mobile,
                exp_from: expFrom,
                exp_to: expTo
            },
            success: function (res) {
                if (res.success) {
                    renderApplications(res.data.applications);
                    updatePagination(res.data.metadata);
                } else {
                    $('#xh-app-list').html('<div class="xh-error">' + (res.data.message || 'Failed to load data') + '</div>');
                }
            },
            error: function () {
                $('#xh-app-list').html('<div class="xh-error">Server Error</div>');
            }
        });
    }

    // Initial Load
    // loadApplications(); // Called by change event of requirement or manually?
    // Let's call it initially
    loadApplications();
});

