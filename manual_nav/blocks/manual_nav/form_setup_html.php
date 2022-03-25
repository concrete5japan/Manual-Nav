<?php defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\File\File;

/** @var \Concrete\Core\View\AbstractView $view */
$displayImage = isset($displayImage) ? $displayImage : 0;
$rows = isset($rows) ? $rows : [];
$icons = isset($icons) ? $icons : [];

$app = Application::getFacadeApplication();
$token = $app->make('helper/validation/token');
?>
<div class="form-group">
    <label><?php echo t('Include Image.'); ?></label>
    <select data-field="entry-image-select" name="displayImage" class="form-control" style="width: 60%;">
        <option value="0" <?php echo $displayImage == 0 ? 'selected' : ''; ?>><?php echo t('Image None'); ?></option>
        <option value="1" <?php echo $displayImage == 1 ? 'selected' : ''; ?>><?php echo t('Use page attribute. Handle name is thumbnail'); ?></option>
        <option value="2" <?php echo $displayImage == 2 ? 'selected' : ''; ?>><?php echo t('Image setting here'); ?></option>
        <option value="3" <?php echo $displayImage == 3 ? 'selected' : ''; ?>><?php echo t('Use font awesome icon'); ?></option>
    </select>
</div>
<script>
    var CCM_EDITOR_SECURITY_TOKEN = "<?php echo $token->generate('editor'); ?>";
    $(document).ready(function () {

        var ccmReceivingEntry = '';
        var manualnavEntriesContainer = $('.ccm-manualnav-entries');
        var _templateSlide = _.template($('#imageTemplate').html());

        var attachDelete = function ($obj) {
            $obj.click(function () {
                var deleteIt = confirm('<?php echo t('Are you sure?'); ?>');
                if (deleteIt == true) {
                    $(this).closest('.ccm-manualnav-entry').remove();
                    doSortCount();
                }
            });
        }

        var attachSortDesc = function ($obj) {
            $obj.click(function () {
                var myContainer = $(this).closest($('.ccm-manualnav-entry'));
                myContainer.insertAfter(myContainer.next('.ccm-manualnav-entry'));
                doSortCount();
            });
        }

        var attachSortAsc = function ($obj) {
            $obj.click(function () {
                var myContainer = $(this).closest($('.ccm-manualnav-entry'));
                myContainer.insertBefore(myContainer.prev('.ccm-manualnav-entry'));
                doSortCount();
            });
        }

        var attachFileManagerLaunch = function ($obj) {
            $obj.click(function () {
                var oldLauncher = $(this);
                ConcreteFileManager.launchDialog(function (data) {
                    ConcreteFileManager.getFileDetails(data.fID, function (r) {
                        jQuery.fn.dialog.hideLoader();
                        var file = r.files[0];
                        oldLauncher.html(file.resultsThumbnailImg);
                        oldLauncher.next('.image-fID').val(file.fID)
                    });
                });
            });
        }

        var doSortCount = function () {
            $('.ccm-manualnav-entry').each(function (index) {
                $(this).find('.ccm-manualnav-entry-sort').val(index);
            });
            ei = $('[name=displayImage]').val()
            if (ei == 0 || ei == 1) {
                $('.set-here-image').hide();
                $('.ccm-block-manualnav-select-icon').hide();
            } else if (ei == 2){
                $('.set-here-image').show();
                $('.ccm-block-manualnav-select-icon').hide();
            } else if (ei == 3){
                $('.set-here-image').hide();
                $('.ccm-block-manualnav-select-icon').show();
            }

        };

        manualnavEntriesContainer.on('change', 'select[data-field=entry-link-select]', function () {
            refreshLinkTypeControls($(this));
        });

        function refreshLinkTypeControls(selector) {
            var container = selector.closest('.ccm-manualnav-entry');
            var linkType = parseInt(selector.val());

            // linkType: None = 0, Page URL = 1, External URL = 2, File = 3
            container.find('div[data-field=entry-link-page-selector]').toggle(linkType === 1);
            container.find('div[data-field=entry-link-url]').toggle(linkType === 2);
            container.find('div[data-field=entry-link-file-selector]').toggle(linkType === 3);
            container.find('div[data-field=entry-link-blank-window]').toggle( linkType > 0);
        };
<?php
if ($rows) {
    foreach ($rows as $row) {
        $image_url = '';
        $f = File::getByID($row['fID']);
        if ($f && $f->getVersion()->getTypeObject()->supportsThumbnails()) {
            $image_url = $f->getThumbnailURL('file_manager_listing');
        }
        $internalLinkFileTitle = '';
        $internalLinkFile = File::getByID($row['internalLinkFID']);
        if ($internalLinkFile) {
            $internalLinkFileTitle = $internalLinkFile->getVersion()->getTitle();
        }
        if ($row['internalLinkFID']) {
            $linkType = 3;
        } elseif ($row['linkURL']) {
            $linkType = 2;
        } elseif ($row['internalLinkCID']) {
            $linkType = 1;
        } else {
            $linkType = 0;
        } ?>
                manualnavEntriesContainer.append(_templateSlide({
                    fID: '<?php echo h($row['fID']); ?>',
                    image_url: '<?php echo h($image_url); ?>',
                    icon: '<?php echo h($row['icon']); ?>',
                    icons: <?php echo json_encode($icons); ?>,
                    link_url: '<?php echo h($row['linkURL']); ?>',
                    link_type: '<?php echo h($linkType); ?>',
                    title: '<?php echo h($row['title']); ?>',
                    sort_order: '<?php echo h($row['sortOrder']); ?>',
                    internalLinkFID: '<?php echo h($row['internalLinkFID']); ?>',
                    internalLinkFileTitle: '<?php echo h($internalLinkFileTitle); ?>',
                    openInNewWindow : '<?php echo $row['openInNewWindow']; ?>'
                }));
                manualnavEntriesContainer.find('.ccm-manualnav-entry:last-child div[data-field=entry-link-page-selector]').concretePageSelector({
                    'inputName': 'internalLinkCID[]', 'cID': <?php echo $linkType === 1 ? (int) $row['internalLinkCID'] : 'false'; ?>
                });

                manualnavEntriesContainer.find('.ccm-manualnav-entry:last-child button[data-field=entry-link-file-selector-select]').each(function () {
                    $(this).on('click', function () {
                        var oldLauncher = $(this);
                        ConcreteFileManager.launchDialog(function(data) {
                            ConcreteFileManager.getFileDetails(data.fID, function (r) {
                                jQuery.fn.dialog.hideLoader();
                                var file = r.files[0];
                                oldLauncher.text(file.title);
                                oldLauncher.next('.image-fID').val(file.fID)
                            })
                        })
                    })
                })
        <?php
    }
}
?>
                    doSortCount();
                    manualnavEntriesContainer.find('select[data-field=entry-link-select]').trigger('change');
                    $('.ccm-add-manualnav-entry').click(function () {
                        var thisModal = $(this).closest('.ui-dialog-content');
                        manualnavEntriesContainer.append(_templateSlide({
                            fID: '',
                            title: '',
                            link_url: '',
                            icon: '',
                            icons: <?php echo json_encode($icons); ?>,
                            cID: '',
                            link_type: 0,
                            sort_order: '',
                            image_url: '',
                            internalLinkFID: 0,
                            internalLinkFileTitle: '',
                            openInNewWindow: 0
                        }));

                        var newSlide = $('.ccm-manualnav-entry').last();
                        thisModal.scrollTop(newSlide.offset().top);
                        attachDelete(newSlide.find('.ccm-delete-manualnav-entry'));
                        attachFileManagerLaunch(newSlide.find('.ccm-pick-manualnav-image'));
                        newSlide.find('div[data-field=entry-link-page-selector-select]').concretePageSelector({
                            'inputName': 'internalLinkCID[]'
                        });
                        newSlide.find('button[data-field=entry-link-file-selector-select]').each(function () {
                            $(this).on('click', function () {
                                var oldLauncher = $(this);
                                ConcreteFileManager.launchDialog(function(data) {
                                    ConcreteFileManager.getFileDetails(data.fID, function (r) {
                                        jQuery.fn.dialog.hideLoader();
                                        var file = r.files[0];
                                        oldLauncher.html(file.title);
                                        oldLauncher.next('.image-fID').val(file.fID)
                                    })
                                })
                            })
                        })

                        attachSortDesc(newSlide.find('i.fa-sort-desc'));
                        attachSortAsc(newSlide.find('i.fa-sort-asc'));
                        doSortCount();
                    });

                    $('[name=displayImage]').change(function () {
                        ei = $(this).val()
                        if (ei == 0 || ei == 1) {
                            $('.set-here-image').hide();
                            $('.ccm-block-manualnav-select-icon').hide();
                        } else if (ei == 2){
                            $('.set-here-image').show();
                            $('.ccm-block-manualnav-select-icon').hide();
                        } else if (ei == 3){
                            $('.set-here-image').hide();
                            $('.ccm-block-manualnav-select-icon').show();
                        }
                    });

                    $(document).on('change', '[name^=openInNewWindowCheck]', function() {
                        $(this).prev().val($(this).prop('checked') ? 1 : 0);
                    });

                    attachDelete($('.ccm-delete-manualnav-entry'));
                    attachSortAsc($('i.fa-sort-asc'));
                    attachSortDesc($('i.fa-sort-desc'));
                    attachFileManagerLaunch($('.ccm-pick-manualnav-image'));
                });

                var iconPreview = function(obj){
                    $(obj).next().removeClass();
                    if($(obj).val()) {
                        <?php if (class_exists(\Concrete\Core\Html\Service\FontAwesomeIcon::class)) { ?>
                        $(obj).next().addClass($(obj).val());
                        <?php } else { ?>
                        $(obj).next().addClass('fa fa-' + $(obj).val());
                        <?php } ?>
                    }
                }
</script>
<style>

    .ccm-manualnav-block-container .redactor_editor {
        padding: 20px;
    }
    .ccm-manualnav-block-container input[type="text"],
    .ccm-manualnav-block-container textarea {
        display: block;
        width: 100%;
    }
    .ccm-manualnav-block-container .btn-success {
        margin-bottom: 20px;
    }

    .ccm-manualnav-entries {
        padding-bottom: 30px;
    }

    .ccm-pick-manualnav-image {
        padding: 15px;
        cursor: pointer;
        background: #dedede;
        border: 1px solid #cdcdcd;
        text-align: center;
        vertical-align: center;
    }

    .ccm-pick-manualnav-image img {
        max-width: 100%;
    }

    .ccm-manualnav-entry {
        position: relative;
    }



    .ccm-manualnav-block-container i.fa-sort-asc {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }

    .ccm-manualnav-block-container i:hover {
        color: #5cb85c;
    }

    .ccm-manualnav-block-container i.fa-sort-desc {
        position: absolute;
        top: 15px;
        cursor: pointer;
        right: 10px;
    }
</style>
<div class="ccm-manualnav-block-container">
    <div class="ccm-manualnav-entries">

    </div>
    <span class="btn btn-success ccm-add-manualnav-entry"><?php echo t('Add Link'); ?></span>
</div>
<script type="text/template" id="imageTemplate">
<div class="ccm-manualnav-entry well">
    <i class="fa fa-sort-desc fas fa-sort-down"></i>
    <i class="fa fa-sort-asc fas fa-sort-up"></i>
    <div class="form-group">
        <span class="btn btn-danger ccm-delete-manualnav-entry"><?php echo t('Delete Link'); ?></span>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group set-here-image">
                <label><?php echo t('Image'); ?></label>
                <div class="ccm-pick-manualnav-image">
                    <% if (image_url.length > 0) { %>
                    <img src="<%= image_url %>" />
                    <% } else { %>
                    <i class="fa fa-picture-o far fa-image"></i>
                    <% } %>
                </div>
                <input type="hidden" name="<?php echo $view->field('fID'); ?>[]" class="image-fID" value="<%=fID%>" />
            </div>
            <div class="form-group ccm-block-manualnav-select-icon" style="margin-right: 5px;">
                <select id="icon2" name="<?php echo $view->field('icon') . '[]'; ?>" class="form-control" onchange="iconPreview(this)">
                    <% _.each(icons,function(val,key){ %>
                        <option value="<%=key%>" <%if(icon == key){%> selected <% } %>><%=val%></option>
                    <% }); %>
                </select>
                <?php if (class_exists(\Concrete\Core\Html\Service\FontAwesomeIcon::class)) { ?>
                    <i data-preview="icon" <%if(icon) { %>class="<%=icon%>"<% } %>></i>
                <?php } else { ?>
                    <i data-preview="icon" <%if(icon) { %>class="fa fa-<%=icon%>"<% } %>></i>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-9">
            <div class="form-group">
                <label><?php echo t('Title'); ?></label>
                <input type="text" name="<?php echo $view->field('title'); ?>[]" value="<%=title%>" />
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo t('Link'); ?></label>
                        <select data-field="entry-link-select" name="linkType[]" class="form-control">
                            <option value="0" <% if (!link_type) { %>selected<% } %>><?php echo t('None'); ?></option>
                            <option value="1" <% if (link_type == 1) { %>selected<% } %>><?php echo t('Another Page'); ?></option>
                            <option value="2" <% if (link_type == 2) { %>selected<% } %>><?php echo t('External URL'); ?></option>
                            <option value="3" <% if (link_type == 3) { %>selected<% } %>><?php echo t('File'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">

                    <div style="display: none;" data-field="entry-link-url" class="form-group">
                        <label><?php echo t('URL:'); ?></label>
                        <input type="text" name="linkURL[]" value="<%=link_url%>">
                    </div>

                    <div style="display: none;" data-field="entry-link-page-selector" class="form-group">
                        <label><?php echo t('Choose Page:'); ?></label>
                        <div data-field="entry-link-page-selector-select"></div>
                    </div>

                    <div style="display: none;" data-field="entry-link-file-selector">
                        <button type="button" class="btn btn-secondary btn-default" data-field="entry-link-file-selector-select">
                            <% if (internalLinkFileTitle) { %>
                            <%- internalLinkFileTitle %>
                            <% } else { %>
                            <?php echo t('Choose File') ?>
                            <% } %>
                        </button>
                        <input type="hidden" name="<?php echo $view->field('internalLinkFID'); ?>[]" class="image-fID" value="<%=internalLinkFID%>" />
                    </div>



                    <input class="ccm-manualnav-entry-sort" type="hidden" name="<?php echo $view->field('sortOrder'); ?>[]" value="<%=sort_order%>"/>
                </div>
            </div>

            <div style="display: none;" data-field="entry-link-blank-window" class="form-group">
                <label>
                    <input type="hidden" name="openInNewWindow[]" value="<% if(openInNewWindow==1){ %>1<% }else{ %>0 <% } %>"/>
                    <input type="checkbox" <% if(openInNewWindow==1){ %>checked <% } %> name="openInNewWindowCheck[<%=sort_order%>]" value="1"  />
                    <?php echo t('Open Link in New Window'); ?>
                </label>
            </div>
        </div>
    </div>
</div>
</script>
