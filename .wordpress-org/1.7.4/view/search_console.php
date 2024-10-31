<style>
    #wpcontent {
        padding-right: 20px;
    }
    .widefat th input, .wp-admin select {
        margin: 0;
        max-width: 100%;
        line-height: 100%;
        padding: 0 4px;
        height: 35px;
        display: block;
    }
    .widefat td p {
        text-align: left;
    }
    select {
        width: 100%;
    }
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }

   #search_console_filter_table {
       margin-bottom: 48px;
   }

   .filter_row {
       width: 100%;
       float: left;
       display: flex;
       align-content: center;
       align-items: center;
       margin-bottom: 8px;
   }
    .filter_row:after {
        content: '';
        clear: both;
    }

    .filter_col {
        width: 40%;
        float: left;
        margin-right: 8px;
    }

    .filter_button {
        width: 15%;
        float: left;
    }

    .filter_button .wp-core-ui .button-group.button-small .button, .wp-core-ui .button.button-small {
        height: 35px;
        line-height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }


</style>
<h1>Search Console</h1>

<table id="search_console_filter_table" class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-title column-primary" style="cursor: pointer;">Title</th>
            <th scope="col" class="manage-column column-page" style="cursor: pointer;">Page</th>
            <th scope="col" class="manage-column column-query" style="cursor: pointer;">Query</th>
            <th scope="col" class="manage-column column-clicks" style="cursor: pointer;">Click</th>
            <th scope="col" class="manage-column column-ctr"  style="cursor: pointer;">CTR</th>
            <th scope="col" class="manage-column column-impressions" style="cursor: pointer;">Impressions</th>
            <th scope="col" class="manage-column column-position" style="cursor: pointer;">Position</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="col" class="manage-column column-title column-primary" style="cursor: pointer;"><input type="text" id="filter_title" onkeyup="filter_table( 'filter_title', 0 )" placeholder="Search for title.."></th>
            <th scope="col" class="manage-column column-page" style="cursor: pointer;"><input type="text" id="filter_page" onkeyup="filter_table( 'filter_page', 1 )" placeholder="Search for url.."></th>
            <th scope="col" class="manage-column column-query" style="cursor: pointer;"><input type="text" id="filter_query" onkeyup="filter_table( 'filter_query', 2 )" placeholder="Search for query.."></th>
            <th scope="col" class="manage-column column-clicks" style="cursor: pointer;">
                <div class="filter_row">
                    <div class="filter_col">
                        <input type="number" class="filter_click" onkeyup="filter_table_numeric( 'filter_click', 3 )" placeholder="N° Clicks">
                    </div>
                    <div class="filter_col">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div class="filter_button">
                        <a href="javascript:void(0);" class="button button-small button-primary" onclick="seoli_addFilterRow( this );"><span class="dashicons dashicons-plus"></span></a>
                    </div>
                </div>
            </th>
            <th scope="col" class="manage-column column-ctr" style="cursor: pointer;">
                <div class="filter_row">
                    <div class="filter_col">
                        <input type="number" class="filter_ctr" onkeyup="filter_table_numeric( 'filter_ctr', 4 )" placeholder="N° CTR">
                    </div>
                    <div class="filter_col">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div class="filter_button">
                        <a href="javascript:void(0);" class="button button-small button-primary" onclick="seoli_addFilterRow( this );"><span class="dashicons dashicons-plus"></span></a>
                    </div>
                </div>
            </th>
            <th scope="col" class="manage-column column-impressions" style="cursor: pointer;">
                <div class="filter_row">
                    <div class="filter_col">
                        <input type="number" class="filter_impressions" onkeyup="filter_table_numeric( 'filter_impressions', 5 )" placeholder="N° Impressions">
                    </div>
                    <div class="filter_col">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div class="filter_button">
                        <a href="javascript:void(0);" class="button button-small button-primary" onclick="seoli_addFilterRow( this );"><span class="dashicons dashicons-plus"></span></a>
                    </div>
                </div>
            </th>
            <th scope="col" class="manage-column column-position" style="cursor: pointer;">
                <div class="filter_row">
                    <div class="filter_col">
                        <input type="number" class="filter_position" onkeyup="filter_table_numeric( 'filter_position', 6 )" placeholder="N° Position">
                    </div>
                    <div class="filter_col">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div class="filter_button">
                        <a href="javascript:void(0);" class="button button-small button-primary" onclick="seoli_addFilterRow( this );"><span class="dashicons dashicons-plus"></span></a>
                    </div>
                </div>
            </th>
            <!--
            <th scope="col" class="manage-column column-post-date" style="cursor: pointer;">
                <div style="width: 100%;float: left;">
                    <div style="width: 50%;float: left">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div style="width: 50%;float: left">
                        <input type="date" id="filter_post_date" onkeyup="filter_table_numeric( 'filter_post_date', 6 )" placeholder="Post Date">
                    </div>
                </div>
                <div style="clear: both";></div>
            </th>
            <th scope="col" class="manage-column column-post-modified" style="cursor: pointer;">
                <div style="width: 100%;float: left;">
                    <div style="width: 50%;float: left">
                        <select>
                            <option selected><</option>
                            <option>=</option>
                            <option>></option>
                        </select>
                    </div>
                    <div style="width: 50%;float: left">
                        <input type="date" id="filter_post_modified" onkeyup="filter_table_numeric( 'filter_post_modified', 7 )" placeholder="Post Modified">
                    </div>
                </div>
                <div style="clear: both";></div>
            </th>
            -->
        </tr>
    </tbody>
</table>

<table id="search_console_table" class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-title column-primary" onclick="sortTable( 0 )" style="cursor: pointer;">Title</th>
            <th scope="col" class="manage-column column-page" onclick="sortTable( 1 )" style="cursor: pointer;">Page</th>
            <th scope="col" class="manage-column column-query" onclick="sortTable( 2 )" style="cursor: pointer;">Query</th>
            <th scope="col" class="manage-column column-clicks" onclick="sortTable( 3 )" style="cursor: pointer;">Click</th>
            <th scope="col" class="manage-column column-ctr" onclick="sortTable( 4 )" style="cursor: pointer;">CTR</th>
            <th scope="col" class="manage-column column-impressions" onclick="sortTable( 5 )" style="cursor: pointer;">Impressions</th>
            <th scope="col" class="manage-column column-position" onclick="sortTable( 6 )" style="cursor: pointer;">Position</th>
            <th scope="col" class="manage-column column-post-date" onclick="sortTable( 7 )" style="cursor: pointer;">Post Date</th>
            <th scope="col" class="manage-column column-post-modified" onclick="sortTable( 8 )" style="cursor: pointer;">Post Modified</th>
            <th scope="col" class="manage-column column-links" onclick="sortTable( 9 )" style="cursor: pointer;">Links</th>
        </tr>
    </thead>
    <tbody id="the-list">
    <?php foreach( $rowData as $row ) : ?>
        <?php $post_id = url_to_postid( $row->page ); ?>
        <?php if( $post_id > 0 ) : ?>
            <tr id="post-<?php echo esc_attr( $post_id );?>" class="iedit author-self level-0 post-<?php echo esc_attr( $post_id );?> type-post status-publish format-standard hentry category-Dummy category">
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <div class="locked-info">
                        <span class="locked-avatar"></span>
                        <span class="locked-text"></span>
                    </div>
                    <strong>
                        <a class="row-title" href="/wp-admin/post.php?post=<?php echo esc_attr( $post_id );  ?>&action=edit" aria-label="“<?php echo esc_attr( get_the_title( $post_id ) ); ?>”"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
                    </strong>
                    <div class="row-actions">
                        <span class="edit"><a href="/wp-admin/post.php?post=<?php echo esc_attr( $post_id );  ?>&action=edit" aria-label="Edit “<?php echo esc_attr( get_the_title( $post_id ) ); ?>”">Edit</a> | </span>
                        <span class="view"><a href="<?php echo esc_url( $row->page ); ?>" rel="bookmark" aria-label="View “<?php echo esc_attr( get_the_title( $post_id ) ); ?>”" target="_blank">View</a></span>
                    </div>
                </td>
                <td class="query column-page" data-colname="Page">
                    <p><?php echo esc_html( $row->page ); ?></p>
                </td>
                <td class="query column-query" data-colname="Query">
                    <p><?php echo esc_html( $row->query ); ?></p>
                </td>
                <td class="clicks column-clicks" data-colname="Clicks">
                    <p><?php echo esc_html( $row->clicks ); ?></p>
                </td>
                <td class="ctr column-ctr" data-colname="CTR">
                    <p><?php echo $row->ctr; ?></p>
                </td>
                <td class="impressions column-impressions" data-colname="Impressions">
                    <p><?php echo esc_html( $row->impressions ); ?></p>
                </td>
                <td class="position column-position" data-colname="Position">
                    <p><?php echo esc_html( $row->position ); ?></p>
                </td>
                <td class="post-date column-post-date" data-colname="Post Date">
                    <p><?php echo esc_html( get_the_date('', $post_id ) ); ?></p>
                </td>
                <td class="post-modified column-post-modified" data-colname="Post Modified">
                    <p><?php echo esc_html( get_the_modified_date('', $post_id ) ); ?></p>
                </td>
                <td class="links column-links" data-colname="Links">
                    <p>
                        <?php
                        $post = get_post( $post_id );
                        $html = $post->post_content;
                        $dom = new DOMDocument;
                        @$dom->loadHTML( $html );
                        $links = $dom->getElementsByTagName('a');
                        echo esc_html( count( $links ) );
                        ?>
                    </p>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th scope="col" class="manage-column column-title column-primary" onclick="sortTable( 0 )" style="cursor: pointer;">Title</th>
            <th scope="col" class="manage-column column-page" onclick="sortTable( 1 )" style="cursor: pointer;">Page</th>
            <th scope="col" class="manage-column column-query" onclick="sortTable( 2 )" style="cursor: pointer;">Query</th>
            <th scope="col" class="manage-column column-clicks" onclick="sortTable( 3 )" style="cursor: pointer;">Click</th>
            <th scope="col" class="manage-column column-ctr" onclick="sortTable( 4 )" style="cursor: pointer;">CTR</th>
            <th scope="col" class="manage-column column-impressions" onclick="sortTable( 5 )" style="cursor: pointer;">Impressions</th>
            <th scope="col" class="manage-column column-position" onclick="sortTable( 6 )" style="cursor: pointer;">Position</th>
            <th scope="col" class="manage-column column-post-date" onclick="sortTable( 7 )" style="cursor: pointer;">Post Date</th>
            <th scope="col" class="manage-column column-post-modified" onclick="sortTable( 8 )" style="cursor: pointer;">Post Modified</th>
            <th scope="col" class="manage-column column-links" onclick="sortTable( 9 )" style="cursor: pointer;">Links</th>
        </tr>
    </tfoot>
</table>
<script>
    function sortTable(n) {
        let table, rows, switching, i, x, y, x_clean, y_clean, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("search_console_table");
        switching = true;
        // Set the sorting direction to ascending:
        dir = "asc";
        /* Make a loop that will continue until
        no switching has been done: */
        while (switching) {
            // Start by saying: no switching is done:
            switching = false;
            rows = table.rows;
            /* Loop through all table rows (except the
            first, which contains table headers): */
            for (i = 2; i < (rows.length - 2); i++) {
                // Start by saying there should be no switching:
                shouldSwitch = false;
                /* Get the two elements you want to compare,
                one from current row and one from the next: */
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                /* Check if the two rows should switch place,
                based on the direction, asc or desc: */

                x_clean = stripHtml( x.innerHTML );
                y_clean = stripHtml( y.innerHTML );

                if( isNumeric( x_clean ) && isNumeric( y_clean ) ) {
                    if ( dir == "asc" ) {
                        if ( Number( x_clean ) > Number( y_clean ) ) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if ( Number( x_clean ) < Number( y_clean ) ) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                } else {
                    if ( dir == "asc" ) {
                        if ( x_clean.toLowerCase() > y_clean.toLowerCase() ) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if ( x_clean.toLowerCase() < y_clean.toLowerCase() ) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
            }
            if ( shouldSwitch ) {
                /* If a switch has been marked, make the switch
                and mark that a switch has been done: */
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                // Each time a switch is done, increase this count by 1:
                switchcount ++;
            } else {
                /* If no switching has been done AND the direction is "asc",
                set the direction to "desc" and run the while loop again. */
                if (switchcount == 0 && dir == "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }

    function stripHtml(html) {
        let tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }

    function isNumeric( str ) {
        if ( typeof str != "string" ) return false // we only process strings!
        return !isNaN( str ) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
            !isNaN( parseFloat( str ) ) // ...and ensure strings of whitespace fail
    }

    function filter_table( id, n ) {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById( id );
        filter = input.value.toUpperCase();
        table = document.getElementById("search_console_table");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[n];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    //function filter_table_numeric( id, n ) {
    function filter_table_numeric( _class, n ) {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue, sort_rule;
        //input = document.getElementById( id );
        var _input = document.querySelectorAll('.' + _class);
        table = document.getElementById("search_console_table");
        tr = table.getElementsByTagName("tr");

        // Default mostro tutto
        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }

        // Poi applico i filtri in logica and - faccio solo display none
        for( j = 0; j < _input.length; j++ ) {
            //filter = input.value.toUpperCase();
            filter = _input[ j ].value.toUpperCase();
            //sort_rule = input.parentElement.parentElement.querySelectorAll( 'select' )[0].value;
            sort_rule = _input[ j ].parentElement.parentElement.querySelectorAll( 'select' )[0].value;

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[n];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if ( evil( parseInt( filter ) + sort_rule + parseInt( txtValue ) ) ) {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
        return;
    }

    function evil(fn) {
        return new Function('return ' + fn)();
    }

    function seoli_addFilterRow( e ) {
        const th = e.closest( 'th');
        const row = document.createElement( 'div' );
        row.classList.add( 'filter_row' );
        const filter_row = th.querySelectorAll( '.filter_row' );
        row.innerHTML = filter_row[0].innerHTML;
        th.appendChild( row );
    }

</script>