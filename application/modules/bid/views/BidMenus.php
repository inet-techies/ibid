<?php if(!empty($menus)): ?>

<div class="accordion accordion-flush" id="menuAccordion">

<?php $i = 1; foreach($menus as $menu): 
    $collapseId = "collapse".$menu['Bmid'];
    $headingId  = "heading".$menu['Bmid'];
?>

    <div class="accordion-item">

        <!-- Accordion Header (Parent Menu) -->
        <h2 class="accordion-header" id="<?= $headingId; ?>">
            <button class="accordion-button collapsed d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId; ?>"
                    aria-expanded="false"
                    aria-controls="<?= $collapseId; ?>">

                <!-- Parent Checkbox -->
                <input type="checkbox"
                       class="form-check-input me-2 parent-checkbox"
                       id="menu_<?= $menu['Bmid']; ?>"
                       data-menu="<?= $menu['Bmid']; ?>"
                       <?= $menu['IsAssigned'] ? 'checked' : ''; ?>
                       onclick="event.stopPropagation(); updatePermission(<?= $roleId ?>, <?= $menu['Bmid']; ?>, this);">

                <label for="menu_<?= $menu['Bmid']; ?>" class="mb-0 fw-bold">
                    <?= $menu['MenuName']; ?>
                </label>

            </button>
        </h2>

        <!-- Accordion Body (Submenus) -->
        <?php if(!empty($menu['children'])): ?>
        <div id="<?= $collapseId; ?>"
             class="accordion-collapse collapse"
             aria-labelledby="<?= $headingId; ?>"
             data-bs-parent="#menuAccordion">

            <div class="accordion-body">

                <?php foreach($menu['children'] as $child): ?>
                    <div class="form-check mb-2 ms-3">

                        <input type="checkbox"
                               class="form-check-input child-checkbox"
                               data-parent="<?= $menu['Bmid']; ?>"
                               id="submenu_<?= $child['Bmid']; ?>"
                               <?= $child['IsAssigned'] ? 'checked' : ''; ?>
                               onclick="updatePermission(<?= $roleId ?>, <?= $child['Bmid']; ?>, this);">

                        <label class="form-check-label"
                               for="submenu_<?= $child['Bmid']; ?>">
                            <?= $child['MenuName']; ?>
                        </label>

                    </div>
                <?php endforeach; ?>

            </div>
        </div>
        <?php endif; ?>

    </div>

<?php endforeach; ?>

</div>

<?php else: ?>
<p class="text-muted">No menus found</p>
<?php endif; ?>

<script>

// When child changes
$(document).on('change', '.child-checkbox', function(){

    let parentId = $(this).data('parent');
    let parent = $('#menu_' + parentId);

    let totalChildren = $('.child-checkbox[data-parent="'+parentId+'"]').length;
    let checkedChildren = $('.child-checkbox[data-parent="'+parentId+'"]:checked').length;

    // If at least one child checked → parent checked
    if(checkedChildren > 0){
        parent.prop('checked', true);
    } else {
        parent.prop('checked', false);
    }
});

// When parent checked → check all children
$(document).on('change', '.parent-checkbox', function(){

    let menuId = $(this).data('menu');
    let children = $('.child-checkbox[data-parent="'+menuId+'"]');

    if($(this).is(':checked')){
        children.prop('checked', true);
    } else {
        children.prop('checked', false);
    }
});

</script>


 