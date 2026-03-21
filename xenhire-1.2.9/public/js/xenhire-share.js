var shareKey = (typeof xenhireShareData !== 'undefined') ? xenhireShareData.shareKey : '';

function toggleAccordion(header) {
    var expanded = header.getAttribute('aria-expanded') === 'true';
    var newExpanded = !expanded;
    header.setAttribute('aria-expanded', newExpanded);

    var body = header.nextElementSibling;
    if (body) {
        body.style.display = newExpanded ? 'block' : 'none';
    }
}

function switchTab(tabName) {
    // Update Tabs
    var tabs = document.querySelectorAll('.xh-tab');
    tabs.forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    // Update Content
    document.querySelectorAll('.xh-tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
}

function copyText(elementId) {
    var text = document.getElementById(elementId).innerText;
    if (text && text !== '...') {
        navigator.clipboard.writeText(text).then(function () {
            showToaster('Copied');
        });
    }
}

function showToaster(message) {
    // Create toaster if it doesn't exist
    var toaster = document.getElementById('xh-toaster');
    if (!toaster) {
        toaster = document.createElement('div');
        toaster.id = 'xh-toaster';
        toaster.className = 'xh-toaster';

        var icon = document.createElement('i');
        icon.className = 'ki-outline ki-check';
        icon.style.marginRight = '8px';
        icon.style.fontSize = '18px';

        var text = document.createElement('span');
        text.id = 'xh-toaster-text';

        toaster.appendChild(icon);
        toaster.appendChild(text);
        document.body.appendChild(toaster);
    }

    document.getElementById('xh-toaster-text').innerText = message;

    // Show
    toaster.classList.add('show');

    // Hide after 3s
    setTimeout(function () {
        toaster.classList.remove('show');
    }, 3000);
}

function prevQuestion(e) {
    e.stopPropagation();
    if (typeof window.currentQIndex !== 'undefined' && window.currentQIndex > 0) {
        window.currentQIndex--;
        renderQuestion(window.currentQIndex);
    }
}

function nextQuestion(e) {
    e.stopPropagation();
    if (typeof window.currentQIndex !== 'undefined' && window.interviewQuestions && window.currentQIndex < window.interviewQuestions.length - 1) {
        window.currentQIndex++;
        renderQuestion(window.currentQIndex);
    }
}

function renderQuestion(index) {
    var data = window.interviewQuestions;
    if (!data || !data[index]) return;

    var item = data[index];
    var total = data.length;
    var qNum = index + 1;

    // Update Counter
    jQuery('#xh-q-counter').text('Question ' + qNum + ' of ' + total);

    // Update Buttons state
    if (index === 0) jQuery('#xh-prev-q').addClass('disabled'); else jQuery('#xh-prev-q').removeClass('disabled');
    if (index === total - 1) jQuery('#xh-next-q').addClass('disabled'); else jQuery('#xh-next-q').removeClass('disabled');

    // Render Content
    //console.log('Rendering Question:', index, item);

    var question = item.Question || 'Question ' + qNum;
    var videoUrl = item.VideoURL || '';
    var answer = item.Answer || '';
    var qType = item.QuestionTypeID;

    var html = '<div class="xh-question-title"><span>Q.' + qNum + '</span> ' + question + '</div>';
    html += '<div class="xh-answer-label">Answer</div>';

    var isVideo = false;
    if (qType == 1 || (!qType && videoUrl && (videoUrl.indexOf('http') === 0 || videoUrl.indexOf('/') === 0) && videoUrl.length > 20)) {
        isVideo = true;
    }

    if (isVideo) {
        html += '<div style="margin-top:10px;"><video src="' + videoUrl + '" controls style="width:100%; max-width:100%; border-radius:8px; max-height:400px;"></video></div>';
    } else if (answer) {
        html += '<div class="xh-answer-box">' + answer + '</div>';
    } else if (videoUrl) {
        // Decode if it's potentially URL encoded text (common for this API)
        var decodedText = videoUrl;
        try {
            decodedText = decodeURIComponent(videoUrl);
        } catch (e) { console.log('Decode error', e); }

        html += '<div class="xh-answer-box">' + decodedText + '</div>';
    } else {
        html += '<div class="xh-answer-box" style="font-style:italic;">Response recorded (Pending review)</div>';
    }

    // --- AI Score Section ---
    // Verify key names from Admin JS or API dump: Matches 'IsAIScored', 'AIScore', 'AIStrengths', 'AIWeaknesses'
    if (item.IsAIScored == 1 || (item.AIScore && item.AIScore > 0)) {
        var score = parseFloat(item.AIScore) || 0;
        var maxStars = 5; // Assuming 0-10 scale maps to 5 stars? Admin JS: Math.round(data.AIScore / 2)
        var starsActive = Math.round(score / 2);

        var starHtml = '';
        for (var i = 1; i <= maxStars; i++) {
            var isFilled = (i <= starsActive);
            var starClass = 'ki-duotone ki-star' + (isFilled ? ' checked' : '');
            starHtml += '<i class="' + starClass + '"></i>';
        }

        html += '<div class="xh-ai-section">';
        html += '<div class="xh-ai-header">';
        html += '<div class="xh-ai-title">AI Score</div>';
        html += '<div class="xh-star-rating">' + starHtml + '</div>';
        html += '</div>';

        // Strengths
        // User requested to show titles even if empty
        html += '<div class="xh-match-box xh-box-green">';
        html += '<div class="xh-match-box-title">Strengths</div>';
        html += '<div class="xh-match-content">' + (item.AIStrengths || '') + '</div>';
        html += '</div>';

        // Improvement Areas
        html += '<div class="xh-match-box xh-box-red">';
        html += '<div class="xh-match-box-title">Improvement Areas</div>';
        html += '<div class="xh-match-content">' + (item.AIWeaknesses || '') + '</div>';
        html += '</div>';

        // Suggestions
        html += '<div class="xh-match-box xh-box-blue">';
        html += '<div class="xh-match-box-title">Suggestions</div>';
        html += '<div class="xh-match-content">' + (item.AISuggestions || '') + '</div>';
        html += '</div>';

        html += '</div>'; // End xh-ai-section
    }

    jQuery('#xh-interview-container').html(html);
}

jQuery(document).ready(function ($) {
    if (!shareKey) return;

    // Fetch Data
    $.ajax({
        url: xenhireAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'xenhire_public_get_share_data',
            nonce: xenhireAjax.nonce,
            share_key: shareKey
        },
        success: function (res) {
            if (res.success) {
                var data = res.data;
                // Handle stringified data if necessary
                // Assuming 'data' is the object we sent from PHP (fields Name, Skills, RawData, etc.)

                // Populate Profile
                $('#xh-name').text(data.Name || 'Unknown');
                $('#xh-job-title').text(data.JobTitle + ', ' || '');

                // Sidebar Company Name
                if (data.RawData && data.RawData.CompanyName) {
                    $('#xh-sidebar-company').text(data.RawData.CompanyName);
                } else {
                    $('#xh-sidebar-company').text('');
                }

                $('#xh-email').text(data.Email || '');
                $('#xh-phone').text(data.Mobile || '');

                // LinkedIn
                if (data.RawData && data.RawData.LinkedInURL) {
                    var linkedin = data.RawData.LinkedInURL;
                    $('#xh-linkedin').attr('href', linkedin).text(linkedin.replace(/^https?:\/\/(www\.)?/, '')); // Shorten text
                    // Add hidden span for copy functionality if needed, or copy href
                    // Actually copyText uses innerText. Let's create a hidden span with full URL for copy?
                    // Or just copy the text which might be shortened? 
                    // Better: make copyText handle href if target is a link, OR just put full url in a hidden attribute.
                    // Simple approach: Put full URL in a data attribute or hidden element.
                    // Let's create a hidden element for copy
                    if ($('#xh-linkedin-href').length === 0) {
                        $('body').append('<span id="xh-linkedin-href" style="display:none;">' + linkedin + '</span>');
                    } else {
                        $('#xh-linkedin-href').text(linkedin);
                    }
                    $('#xh-linkedin-box').show();
                } else {
                    $('#xh-linkedin-box').hide();
                }

                $('#xh-location').text(data.CurrentCity || 'Not Specified');
                $('#xh-salary').text(data.CurrentSalary ? data.CurrentSalary : '');
                $('#xh-exp-label').text(data.Experience ? (data.Experience + ' Years') : 'Fresher');

                if (data.PhotoURL) {
                    $('#xh-img').attr('src', data.PhotoURL);
                }

                // 1. Resume Visibility
                if (data.ResumeURL && data.ResumeURL !== '') {
                    $('#xh-resume-card').show();

                    // Use Google Docs Viewer for now if it's external, or simple Object/Iframe
                    // Or just a link if it can't be embedded easily without CORS
                    var url = data.ResumeURL;
                    // Just listing a button as redundant fallback, lets do iframe if PDF
                    var isPdf = url.toLowerCase().indexOf('.pdf') > -1;

                    if (isPdf) {
                        $('#xh-resume-container').html('<iframe src="' + url + '" class="xh-resume-frame"></iframe>');
                    } else {
                        // Office viewer for docs
                        var officeUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(url);
                        $('#xh-resume-container').html('<iframe src="' + officeUrl + '" class="xh-resume-frame"></iframe>');
                    }
                } else {
                    $('#xh-resume-card').hide();
                }

                // 2. Skills
                if (data.Skills) {
                    var skills = data.Skills.split(',').map(s => s.trim()).filter(s => s);
                    if (skills.length > 0) {
                        var html = '';
                        skills.forEach(function (s) {
                            html += '<span class="xh-tag">' + s + '</span>';
                        });
                        $('#xh-skills-list').html(html);
                    }
                }

                // 4. Match Score Badge (Header)
                if (data.MatchName) {
                    var color = (data.RawData && data.RawData.MatchColor) ? data.RawData.MatchColor : '#6b7280';
                    var badgeHtml = '<span style="display:inline-block; width:12px; height:12px; background-color:' + color + '; border-radius:50%; margin-right:8px; vertical-align:middle;"></span>';
                    badgeHtml += '<span style="vertical-align:middle; color:' + color + '; font-weight:600; font-size:14px;">' + data.MatchName + '</span>';

                    $('#xh-match-badge').html(badgeHtml).show();
                }

                // 5. Interview Data
                if (data.InterviewData && data.InterviewData.length > 0) {
                    $('#xh-interview-card').show();

                    // Populate Company Name if available
                    if (data.RawData && data.RawData.CompanyName) {
                        $('#xh-int-company').text(data.RawData.CompanyName);
                    } else {
                        $('#xh-int-company').text('');
                    }

                    // Store global data for navigation
                    window.interviewQuestions = data.InterviewData;
                    window.currentQIndex = 0;

                    renderQuestion(0);

                } else {
                    $('#xh-interview-card').hide();
                }

                // Helper for Duration
                function calculateDuration(start, end) {
                    if (!start) return '';
                    var s = new Date(start);
                    var e = end ? new Date(end) : new Date();
                    if (isNaN(s.getTime())) return '';

                    var diff = e.getTime() - s.getTime();
                    var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    var years = Math.floor(days / 365);
                    var months = Math.floor((days % 365) / 30);

                    var str = '';
                    if (years > 0) str += years + ' yr' + (years > 1 ? 's' : '') + ' ';
                    if (months > 0) str += months + ' mo' + (months > 1 ? 's' : '') + ' ';
                    return str.trim();
                }

                // 6. Experience List
                if (data.EmploymentData && data.EmploymentData.length > 0) {
                    var empHtml = '';
                    data.EmploymentData.forEach(function (emp) {
                        var designation = emp.Designation || emp.JobTitle || '';
                        var company = emp.Organization || emp.CompanyName || '';
                        var desc = emp.Description || emp.Responsibilities || emp.Roles || emp.Summary || emp.JobDescription || ''; // Fallback for description

                        // Date Logic (Matched with Admin)
                        var startDateStr = '';
                        var endDateStr = '';
                        var durationStr = '';

                        // Helper to format date "01 Jan 1999"
                        var formatDate = function (y, m) {
                            if (!y) return '';
                            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                            var mIndex = parseInt(m) - 1;
                            if (isNaN(mIndex) || mIndex < 0 || mIndex > 11) mIndex = 0;
                            return '01 ' + monthNames[mIndex] + ' ' + y;
                        };

                        if (emp.StartYear) {
                            startDateStr = formatDate(emp.StartYear, emp.StartMonth);

                            if (emp.IsCurrent) {
                                endDateStr = 'Present';
                            } else if (emp.EndYear) {
                                endDateStr = formatDate(emp.EndYear, emp.EndMonth);
                            } else {
                                endDateStr = 'Present';
                            }

                            // Calculate Duration from Year/Month directly for better accuracy
                            var start = new Date(emp.StartYear, (parseInt(emp.StartMonth) || 1) - 1);
                            var end = new Date();
                            if (!emp.IsCurrent && emp.EndYear) {
                                end = new Date(emp.EndYear, (parseInt(emp.EndMonth) || 1) - 1);
                            }

                            var diffMonths = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
                            if (diffMonths > 0) {
                                var years = Math.floor(diffMonths / 12);
                                var months = diffMonths % 12;
                                if (years > 0) durationStr += years + ' yr' + (years > 1 ? 's' : '');
                                if (months > 0) durationStr += ' ' + months + ' mo' + (months > 1 ? 's' : '');
                            }

                        } else if (emp.FromDate || emp.StartDate) {
                            // Fallback
                            startDateStr = emp.FromDate || emp.StartDate;
                            endDateStr = emp.ToDate || emp.EndDate || 'Present';
                            durationStr = calculateDuration(startDateStr, (endDateStr === 'Present' ? null : endDateStr));
                        }

                        // Construct Date Display
                        var dateDisplay = '';
                        if (startDateStr) {
                            dateDisplay = startDateStr;
                            if (endDateStr) dateDisplay += ' - ' + endDateStr;
                            if (durationStr) dateDisplay += ' • ' + durationStr;
                        }

                        if (designation || company) {
                            empHtml += '<div class="xh-exp-item">';

                            // Header Row
                            empHtml += '<div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:6px; flex-wrap:wrap;">';

                            // Left: Designation, Company
                            empHtml += '<div style="font-weight:700; color:#0f172a; font-size:15px; margin-right:10px;">';
                            empHtml += '<span style="color:#0f172a;">' + designation + '</span>';
                            if (company) {
                                empHtml += ', <span style="color:#0f172a;">' + company + '</span>';
                            }
                            empHtml += '</div>';

                            // Right: Date • Duration
                            if (dateDisplay) {
                                empHtml += '<div style="font-size:13px; color:#64748b; white-space:nowrap;">' + dateDisplay + '</div>';
                            }

                            empHtml += '</div>'; // End Header

                            // Body: Description
                            if (desc) {
                                empHtml += '<div style="font-size:14px; color:#64748b; line-height:1.5;">' + desc + '</div>';
                            }

                            empHtml += '</div>';
                        }
                    });

                    if (empHtml) {
                        $('#xh-experience-list').html(empHtml);
                    }
                }

                // 8. Match Score Data
                var matchHtml = '';
                if (data.MatchName) {
                    matchHtml += '<div style="margin-bottom:8px; font-weight:700; font-size:16px;">' + data.MatchName + '</div>';
                }
                if (data.MatchDescription) {
                    matchHtml += '<div style="margin-bottom:8px; color:#4b5563; font-size:14px; line-height:1.6;">' + data.MatchDescription + '</div>';
                }

                // Static Note
                matchHtml += '<div style="margin-bottom:25px; color:#9ca3af; font-size:13px;">Note: The match score is based on the candidate\'s resume and application only, excluding interview results.</div>';

                // Helper for lists inside boxes
                var processList = function (text, color) {
                    if (!text) return '';
                    if (text.trim().indexOf('<') === 0) {
                        return '<div style="color:' + color + '; line-height:1.5;">' + text + '</div>';
                    }
                    var items = text.split(/\\n|•|- /).map(function (s) { return s.trim(); }).filter(function (s) { return s.length > 0; });
                    if (items.length === 0) return '';
                    var ul = '<ul style="list-style-type: disc; padding-left: 20px; margin-top:5px; margin-bottom:15px; color:' + color + ';">';
                    items.forEach(function (item) {
                        ul += '<li style="margin-bottom:4px;">' + item + '</li>';
                    });
                    ul += '</ul>';
                    return ul;
                };

                // Helper for tags
                var processTags = function (text, bgColor, color) {
                    if (!text) return '';
                    var tags = text.split(',').map(function (s) { return s.trim(); }).filter(function (s) { return s.length > 0; });
                    if (tags.length === 0) return '';
                    var div = '<div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">';
                    tags.forEach(function (tag) {
                        div += '<span style="background-color:' + bgColor + '; color:' + color + '; padding: 4px 12px; border-radius: 4px; font-size: 13px;">' + tag + '</span>';
                    });
                    div += '</div>';
                    return div;
                };

                // Strengths Box (Green)
                if (data.MatchStrengths || (data.MatchSkillsYes && data.MatchSkillsYes !== 'None')) {
                    matchHtml += '<div class="xh-match-box xh-box-green">';
                    matchHtml += '<div class="xh-match-box-title">Strengths</div>';
                    if (data.MatchStrengths) {
                        matchHtml += processList(data.MatchStrengths, '#064e3b');
                    }
                    if (data.MatchSkillsYes && data.MatchSkillsYes !== 'None') {
                        matchHtml += processTags(data.MatchSkillsYes, '#d1fae5', '#065f46');
                    } else if (data.MatchSkillsYes === 'None') {
                        // If explicit "None", show it as a tag? Screenshot showed "None" tag.
                        matchHtml += processTags('None', '#d1fae5', '#065f46');
                    }
                    matchHtml += '</div>';
                }

                // Weaknesses Box (Red)
                if (data.MatchWeaknesses || data.MatchSkillsNo) {
                    matchHtml += '<div class="xh-match-box xh-box-red">';
                    matchHtml += '<div class="xh-match-box-title">Improvement Areas</div>';
                    if (data.MatchWeaknesses) {
                        matchHtml += processList(data.MatchWeaknesses, '#7f1d1d');
                    }
                    if (data.MatchSkillsNo) {
                        matchHtml += processTags(data.MatchSkillsNo, '#fee2e2', '#991b1b');
                    }
                    matchHtml += '</div>';
                }

                if (!matchHtml) {
                    matchHtml = '<div class="xh-cd-empty-state">No match score details available</div>';
                }

                $('#xh-match-score-details').html(matchHtml);

                // 7. Education List
                if (data.EducationData && data.EducationData.length > 0) {
                    var eduHtml = '';
                    data.EducationData.forEach(function (edu) {
                        var degree = edu.Qualification || edu.Degree || 'Unknown Degree';
                        var school = edu.Institute || edu.University || edu.School || 'Unknown Institute';
                        var spec = edu.Specialization ? ' (' + edu.Specialization + ')' : '';

                        // Date Logic
                        var dates = edu.Dates || '';
                        if (!dates) {
                            if (edu.StartYear) dates += edu.StartYear;
                            if (edu.EndYear) dates += ' to ' + edu.EndYear;
                        }
                        if (!dates && edu.PassingYear) {
                            dates = 'Class of ' + edu.PassingYear;
                        }

                        eduHtml += '<div class="xh-edu-item"';

                        // Row 1: Degree (Bold, Dark Blue/Black)
                        eduHtml += '<div class="xh-list-title">';
                        eduHtml += degree + spec;
                        eduHtml += '</div>';

                        // Row 2: Institute (Regular, Slate)
                        eduHtml += '<div class="xh-list-subtitle">';
                        eduHtml += school;
                        eduHtml += '</div>';

                        // Row 3: Dates (Lighter Slate)
                        if (dates) {
                            eduHtml += '<div class="xh-list-subtitle">';
                            eduHtml += dates;
                            eduHtml += '</div>';
                        }

                        eduHtml += '</div>';
                    });

                    if (eduHtml) {
                        $('#xh-education-list').html(eduHtml);
                    }
                } else if (data.Education) {
                    // Fallback to simple string
                    $('#xh-education-list').html('<div class="xh-list-item"><div class="xh-list-title">' + data.Education + '</div></div>');
                }

            } else {
                console.error('Failed to load data', res);
                $('.xh-profile-name').text('Error loading data');
            }
        },
        error: function () {
            console.error('Network Error');
            $('.xh-profile-name').text('Network Error');
        }
    });
});

