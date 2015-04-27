<div id="pages-controller-cms-content" class="cms-content center " data-layout-type="border" data-pjax-fragment="Content">
    <div class="cms-content-header north">
        <div class="cms-content-header-info">
            <% if CurrentMessage %>
                <a class="ss-ui-button" data-icon="back" href="/admin/mandrill" data-pjax-target="Content"><% _t('BackLink_Button_ss.Back') %></a>
            <% end_if %>
            <% include CMSBreadcrumbs %>
        </div>
    </div>

    <div class="cms-content-fields center ui-widget-content cms-panel-padded">


                    $EditForm

    </div>
</div>