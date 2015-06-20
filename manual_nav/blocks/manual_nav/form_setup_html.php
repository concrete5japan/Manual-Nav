<?php  defined('C5_EXECUTE') or die("Access Denied.");

$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();
?>
<script>
    var CCM_EDITOR_SECURITY_TOKEN = "<?php echo Loader::helper('validation/token')->generate('editor')?>";
    $(document).ready(function(){
        var ccmReceivingEntry = '';
        var manualnavEntriesContainer = $('.ccm-manual-nav-entries');
        var _templateSlide = _.template($('#imageTemplate').html());
        var attachDelete = function($obj) {
            $obj.click(function(){
                var deleteIt = confirm('<?php echo t('Are you sure?') ?>');
                if(deleteIt == true) {
                    $(this).closest('.ccm-manual-nav-entry').remove();
                    doSortCount();
                }
            });
        }

        var attachSortDesc = function($obj) {
            $obj.click(function(){
               var myContainer = $(this).closest($('.ccm-manual-nav-entry'));
               myContainer.insertAfter(myContainer.next('.ccm-manual-nav-entry'));
               doSortCount();
            });
        }

        var attachSortAsc = function($obj) {
            $obj.click(function(){
                var myContainer = $(this).closest($('.ccm-manual-nav-entry'));
                myContainer.insertBefore(myContainer.prev('.ccm-manual-nav-entry'));
                doSortCount();
            });
        }

        var doSortCount = function(){
            $('.ccm-manual-nav-entry').each(function(index) {
                $(this).find('.ccm-manual-nav-entry-sort').val(index);
            });
        };

        manualnavEntriesContainer.on('change', 'select[data-field=entry-link-select]', function() {
            var container = $(this).closest('.ccm-manual-nav-entry');
            switch(parseInt($(this).val())) {
                case 2:
                    container.find('div[data-field=entry-link-page-selector]').hide();
                    container.find('div[data-field=entry-link-url]').show();
                    break;
                case 1:
                    container.find('div[data-field=entry-link-url]').hide();
                    container.find('div[data-field=entry-link-page-selector]').show();
                    break;
                default:
                    container.find('div[data-field=entry-link-page-selector]').hide();
                    container.find('div[data-field=entry-link-url]').hide();
                    break;
            }
        });

       <?php if($rows) {
           foreach ($rows as $row) {
            $linkType = 0;
            if ($row['linkURL']) {
                $linkType = 2;
            } else if ($row['internalLinkCID']) {
                $linkType = 1;
           } ?>
           manualnavEntriesContainer.append(_templateSlide({
                link_url: '<?php echo $row['linkURL'] ?>',
                link_type: '<?php echo $linkType?>',
                title: '<?php echo addslashes($row['title']) ?>',
                sort_order: '<?php echo $row['sortOrder'] ?>'
            }));
            manualnavEntriesContainer.find('.ccm-manual-nav-entry:last-child div[data-field=entry-link-page-selector]').concretePageSelector({
                'inputName': 'internalLinkCID[]', 'cID': <?php if ($linkType == 1) { ?><?php echo intval($row['internalLinkCID'])?><?php } else { ?>false<?php } ?>
            });
        <?php }
        }?>

        doSortCount();
        manualnavEntriesContainer.find('select[data-field=entry-link-select]').trigger('change');

        $('.ccm-add-manual-nav-entry').click(function(){
           var thisModal = $(this).closest('.ui-dialog-content');
            manualnavEntriesContainer.append(_templateSlide({
                title: '',
                link_url: '',
                cID: '',
                link_type: 0,
                sort_order: '',
                image_url: ''
            }));
            var newSlide = $('.ccm-manual-nav-entry').last();
            thisModal.scrollTop(newSlide.offset().top);

            attachDelete(newSlide.find('.ccm-delete-manual-nav-entry'));
            newSlide.find('div[data-field=entry-link-page-selector-select]').concretePageSelector({
                'inputName': 'internalLinkCID[]'
            });
            attachSortDesc(newSlide.find('i.fa-sort-desc'));
            attachSortAsc(newSlide.find('i.fa-sort-asc'));
            doSortCount();
        });
        attachDelete($('.ccm-delete-manual-nav-entry'));
        attachSortAsc($('i.fa-sort-asc'));
        attachSortDesc($('i.fa-sort-desc'));
    });
</script>
<style>

    .ccm-manual-nav-block-container .redactor_editor {
        padding: 20px;
    }
    .ccm-manual-nav-block-container input[type="text"],
    .ccm-manual-nav-block-container textarea {
        display: block;
        width: 100%;
    }
    .ccm-manual-nav-block-container .btn-success {
        margin-bottom: 20px;
    }

    .ccm-manual-nav-entries {
        padding-bottom: 30px;
    }

    .ccm-manual-nav-entry {
        position: relative;
    }



    .ccm-manual-nav-block-container i.fa-sort-asc {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }

    .ccm-manual-nav-block-container i:hover {
        color: #5cb85c;
    }

    .ccm-manual-nav-block-container i.fa-sort-desc {
        position: absolute;
        top: 15px;
        cursor: pointer;
        right: 10px;
    }
</style>
<div class="ccm-manual-nav-block-container">
    <span class="btn btn-success ccm-add-manual-nav-entry"><?php echo t('Add Link') ?></span>
    <div class="ccm-manual-nav-entries">

    </div>
</div>
<script type="text/template" id="imageTemplate">
    <div class="ccm-manual-nav-entry well">
        <i class="fa fa-sort-desc"></i>
        <i class="fa fa-sort-asc"></i>

        <div class="form-group">
            <label><?php echo t('Title') ?></label>
            <input type="text" name="<?php echo $view->field('title')?>[]" value="<%=title%>" />
        </div>
        <div class="form-group">
           <label><?php echo t('Link') ?></label>
            <select data-field="entry-link-select" name="linkType[]" class="form-control" style="width: 60%;">
                <option value="0" <% if (!link_type) { %>selected<% } %>><?php echo t('None')?></option>
                <option value="1" <% if (link_type == 1) { %>selected<% } %>><?php echo t('Another Page')?></option>
                <option value="2" <% if (link_type == 2) { %>selected<% } %>><?php echo t('External URL')?></option>
            </select>
        </div>

        <div style="display: none;" data-field="entry-link-url" class="form-group">
           <label><?php echo t('URL:') ?></label>
            <textarea name="linkURL[]"><%=link_url%></textarea>
        </div>

        <div style="display: none;" data-field="entry-link-page-selector" class="form-group">
           <label><?php echo t('Choose Page:') ?></label>
            <div data-field="entry-link-page-selector-select"></div>
        </div>

        <input class="ccm-manual-nav-entry-sort" type="hidden" name="<?php echo $view->field('sortOrder')?>[]" value="<%=sort_order%>"/>
        <div class="form-group">
            <span class="btn btn-danger ccm-delete-manual-nav-entry"><?php echo t('Delete Link'); ?></span>
        </div>
    </div>
</script>
