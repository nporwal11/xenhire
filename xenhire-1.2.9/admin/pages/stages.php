<?php 
if (!defined('ABSPATH')) exit;

// Pagination parameters
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$xenhire_page_no = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$xenhire_page_size = 100;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

// Fetch stages data from API
$xenhire_stages_data = XenHire_API::call('List_Stage', array(
    array('Key' => 'IsActive', 'Value' => -1),
    array('Key' => 'Search', 'Value' => $search),
    array('Key' => 'PageNo', 'Value' => $xenhire_page_no),
    array('Key' => 'PageSize', 'Value' => $xenhire_page_size)
));

$xenhire_stages = array();

if ($xenhire_stages_data['success']) {
    $xenhire_data = json_decode($xenhire_stages_data['data'], true);
    
    // Extract stages from first array
    if (isset($xenhire_data[0]) && is_array($xenhire_data[0])) {
        $xenhire_stages = $xenhire_data[0];
    }
}
?>

<div class="wrap xenhire-stages-page xenhire-common">
    <!-- Header with Back Button -->
    <div class="xenhire-page-header">
        <div class="xenhire-header-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-settings')); ?>" class="xenhire-back-btn">
                <i class="ki-duotone ki-left ki-fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </a>
            <h1>Stages</h1>
        </div>
    </div>

    <div class="xenhire-container">
        <!-- Search Bar -->
         <div class="xh-card">
            <div class="xenhire-toolbar">
                <div id="xenhire-stages-search-form">
                    <div class="xenhire-search-box">
                        <i class="ki-outline ki-magnifier ki-fs-2"></i>
                        <input type="text" id="kt_filter_search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search" />
                        <button type="button" class="xenhire-search-clear-btn" style="display: <?php echo $search ? 'flex' : 'none'; ?>;">
                            <i class="ki-outline ki-cross ki-fs-2"></i>
                        </button>
                    </div>
                </div>
                <button id="kt-btn-create" class="xh-btn-primary">
                    Create New Stage
                </button>
            </div>

            <?php if ($xenhire_stages_data['success']): ?>
            
            <!-- Stages Table -->
            <div class="xenhire-table-container">
                <table class="wp-list-table widefat fixed xenhire-stages-table">
                    <thead id="tHead">
                        <tr>
                            <th class="column-name">Name</th>
                            <th class="column-color">Color</th>
                            <th class="column-position">Position</th>
                            <th class="column-template">Email Template</th>
                            <th class="column-actions" style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tBody">
                        <?php if (!empty($xenhire_stages)): ?>
                            <?php foreach ($xenhire_stages as $xenhire_index => $xenhire_stage): ?>
                                <tr class="<?php echo $xenhire_index % 2 === 0 ? 'even' : 'odd'; ?>">
                                    <td class="column-name">
                                        <strong><?php echo esc_html($xenhire_stage['Name']); ?></strong>
                                    </td>
                                    <td class="column-color">
                                        <div class="xenhire-color-box" style="background-color: <?php echo esc_attr($xenhire_stage['Color']); ?>"></div>
                                    </td>
                                    <td class="column-position" style="text-align: left; padding-left: 45px;">
                                        <?php echo esc_html($xenhire_stage['OrdPos']); ?>
                                    </td>
                                    <td class="column-template">
                                        <?php 
                                            if (isset($xenhire_stage['EmailTemplate']) && !empty($xenhire_stage['EmailTemplate'])) {
                                                $xenhire_template_display = html_entity_decode($xenhire_stage['EmailTemplate'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $xenhire_template_display = wp_strip_all_tags($xenhire_template_display);
                                                $xenhire_template_display = trim(preg_replace('/\s+/', ' ', $xenhire_template_display));
                                                echo esc_html($xenhire_template_display);
                                            } else {
                                                echo '<span style="color: #999;">Not Assigned</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="column-actions">
                                        <button type="button" class="xh-btn xh-secondary xenhire-edit-stage" 
                                                data-id="<?php echo esc_attr($xenhire_stage['ID']); ?>"
                                                data-name="<?php echo esc_attr($xenhire_stage['Name']); ?>"
                                                data-color="<?php echo esc_attr($xenhire_stage['Color']); ?>"
                                                data-ordpos="<?php echo esc_attr($xenhire_stage['OrdPos']); ?>"
                                                data-emailtemplateid="<?php echo esc_attr($xenhire_stage['EmailTemplateID'] ?? ''); ?>">
                                            Edit
                                        </button>
                                        <?php if ($xenhire_stage['OrdPos'] != 1): ?>
                                            <button type="button" class="xh-btn xh-danger xenhire-delete-stage" data-id="<?php echo esc_attr($xenhire_stage['ID']); ?>">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    <p style="color: #999; font-size: 16px;">No stages found.</p>
                                    <?php if ($search): ?>
                                        <p><a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-stages')); ?>">Clear search</a></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
                <div class="notice notice-error">
                    <p><strong>Error:</strong> <?php echo esc_html($xenhire_stages_data['message']); ?></p>
                    <p>Unable to load stages data. Please try again.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Stage Modal -->
<div class="xenhire-modal fade" id="kt_modal_stage" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="xenhire-modal-overlay"></div>
    <div class="xenhire-modal-dialog modal-dialog-centered">
        <div class="xenhire-modal-content">
            <!-- Modal Header -->
            <div class="xenhire-modal-header">
                <h2 id="headTitle">Add Stage</h2>
                <button type="button" class="xenhire-modal-close" data-dismiss="modal">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="xenhire-modal-body scroll-y">
                <form id="kt_modal_stage_form" class="xenhire-stage-form" action="#">
                    <input type="hidden" id="ID" name="ID" value="-1" />
                    
                    <!-- Stage Name -->
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label required">Stage Name</label>
                        <div class="position-relative">
                            <input type="text" class="xenhire-form-control" placeholder="Name" name="Name" id="Name" value="" />
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Stage Color -->
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label required">Stage Color</label>
                        <div class="position-relative">
                            <input type="color" class="xenhire-color-control" name="Color" id="Color" value="#000000" />
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Position -->
                    <div class="xenhire-form-row fv-row ordpos">
                        <label class="xenhire-form-label required">Position</label>
                        <div class="position-relative">
                            <select id="OrdPos" name="OrdPos" class="xenhire-form-control">
                                <option value="1" disabled="disabled">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Email Template - Loaded dynamically via AJAX -->
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label required">Email Template</label>
                        <div class="position-relative">
                            <select id="EmailTemplateID" name="EmailTemplateID" class="xenhire-form-control" required>
                                <option value="">Loading templates...</option>
                            </select>
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="xenhire-modal-actions">
                        <button type="button" class="xh-btn xh-btn-large xh-secondary xenhire-btn-cancel" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" id="kt_modal_stage_submit" class="xh-btn xh-btn-large xh-primary">
                            <span class="indicator-label">Save</span>
                            <span class="indicator-progress" style="display: none;">
                                Please wait...
                                <span class="spinner is-active"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



