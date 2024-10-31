<style>
    #wpcontent {
        padding-right: 20px;
    }
    fieldset, th {
        text-align: center !important;
    }
</style>

<h1>Export</h1>
<table id="search_console_export_table" class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-title column-primary" style="cursor: pointer;">Title</th>
            <th scope="col" class="manage-column column-page column-primary" style="cursor: pointer;">Page</th>
            <th scope="col" class="manage-column column-query" style="cursor: pointer;">Query</th>
            <th scope="col" class="manage-column column-clicks" style="cursor: pointer;">Click</th>
            <th scope="col" class="manage-column column-ctr" style="cursor: pointer;">CTR</th>
            <th scope="col" class="manage-column column-impressions" style="cursor: pointer;">Impressions</th>
            <th scope="col" class="manage-column column-position" style="cursor: pointer;">Position</th>
            <th scope="col" class="manage-column column-post-date" style="cursor: pointer;">Post Date</th>
            <th scope="col" class="manage-column column-post-modified" style="cursor: pointer;">Post Modified</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_title">
                        <input name="checkbox_title" type="checkbox" id="checkbox_title" value="title">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_page">
                        <input name="checkbox_page" type="checkbox" id="checkbox_page" value="page">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_query">
                        <input name="checkbox_query" type="checkbox" id="checkbox_query" value="query">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_click">
                        <input name="checkbox_click" type="checkbox" id="checkbox_click" value="clicks">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_ctr">
                        <input name="checkbox_ctr" type="checkbox" id="checkbox_ctr" value="ctr">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_impressions">
                        <input name="checkbox_impressions" type="checkbox" id="checkbox_impressions" value="impressions">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_position">
                        <input name="checkbox_position" type="checkbox" id="checkbox_position" value="position">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_post_date">
                        <input name="checkbox_post_date" type="checkbox" id="checkbox_post_date" value="post_date">
                    </label>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span>checkbox</span>
                    </legend>
                    <label for="checkbox_post_modified">
                        <input name="checkbox_post_modified" type="checkbox" id="checkbox_post_modified" value="post_modified">
                    </label>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 48px">
    <p>
        <a href="javascript:void(0);" onclick="seoli_export_csv()" class="button button-primary button-hero">Export in CSV</a>
        <a href="javascript:void(0);" onclick="seoli_export_gsheet()" class="button button-primary button-hero">Export in Google Sheet</a>
    </p>
</div>

<script>
    function seoli_export_csv() {
        let table = document.getElementById("search_console_export_table");
        let rows = table.rows;
        let checkboxes = rows[1].querySelectorAll('input[type=checkbox]:checked');
        let j = 0;
        let download_string = '&seoli_download_report=true&';
        for( j = 0; j < checkboxes.length; j++ ) {
            download_string += 'seoli_data[]=' + checkboxes[j].value + '&';
        }
        window.location.href = window.location.href + encodeURI( download_string );
    }
</script>