<?php
    $graphicsDir = $vars['url'] . "_graphics";
?>

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
strike, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
    margin: 0;
    padding: 0;
    border: 0;
    outline: 0;
    font-weight: inherit;
    font-style: inherit;
    font-size: 100%;
    font-family: inherit;
    vertical-align: baseline;
}

:focus {
    outline: 0;
}
em, i {
    font-style:italic;
}

table {
    border-collapse: separate;
    border-spacing: 0;
}
caption, th, td {
    text-align: left;
    font-weight: normal;
    vertical-align: top;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content: "";
}
blockquote, q {
    quotes: "" "";
}
.clearfloat { 
    clear:both;
    height:0;
    font-size: 1px;
    line-height: 0px;
}

body 
{
    text-align:left;
    margin:0 auto;
    padding:0;
    background: #dedede;
    font: 80%/1.4  "Lucida Grande", Verdana, sans-serif;
    color: #333333;
}
a {
    color: #4690d6;
    text-decoration: none;
    -moz-outline-style: none;
    outline: none;
}
a:visited {
    
}
a:hover {
    color: #0054a7;
    text-decoration: underline;
}
p {
    margin: 0px 0px 15px 0;
}
img {
    border: none;
}
ul {
    margin: 0px 0px 15px;
    padding-left: 20px;
}
ul li {
    margin: 0px;
}
ol {
    margin: 0px 0px 15px;
    padding-left: 20px;
}
ul li {
    margin: 0px;
}
form {
    margin: 0px;
    padding: 0px;
}
small {
    font-size: 90%;
}
h1, h2, h3, h4, h5, h6 {
    font-weight: bold;
    line-height: normal;
}
h1 { font-size: 1.8em; }
h2 { font-size: 1.5em; }
h3 { font-size: 1.2em; }
h4 { font-size: 1.0em; }
h5 { font-size: 0.9em; }
h6 { font-size: 0.8em; }

dt {
    margin: 0;
    padding: 0;
    font-weight: bold;
}
dd {
    margin: 0 0 1em 1em;
    padding: 0;
}
pre, code {
    font-family:Monaco,"Courier New",Courier,monospace;
    font-size:12px;
    background:#EBF5FF;
    overflow:auto;
    
    overflow-x: auto; /* Use horizontal scroller if needed; for Firefox 2, not needed in Firefox 3 */
    white-space: pre-wrap; /* css-3 */
    white-space: -moz-pre-wrap !important; /* Mozilla, since 1999 */
    white-space: -pre-wrap; /* Opera 4-6 */
    white-space: -o-pre-wrap; /* Opera 7 */
    word-wrap: break-word; /* Internet Explorer 5.5+ */
    
}
code {
    padding:2px 3px;
}
pre {
    padding:3px 15px;
    margin:0px 0 15px 0;
    line-height:1.3em;
}
blockquote {
    padding:3px 15px;
    margin:0px 0 15px 0;
    line-height:1.3em;
    background:#EBF5FF;
    border:none !important;
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
}
blockquote p {
    margin:0 0 5px 0;
}

/* canvas layout: 1 column, no sidebar */
#one_column {
/*  width:928px; */
    margin:0;
    min-height: 360px;
    background: #dedede;
    padding:0 0 10px 0;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
}

/* canvas layout: 2 column left sidebar */
#two_column_left_sidebar {
    width:210px;
    margin:0 20px 0 0;
    min-height:360px;
    float:left;
    background: #dedede;
    padding:0px;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    border-bottom:1px solid #cccccc;
    border-right:1px solid #cccccc;
}

#two_column_left_sidebar_maincontent {
    width:718px;
    margin:0;
    min-height: 360px;
    float:left;
    background: #dedede;
    padding:0 0 5px 0;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
}

#two_column_left_sidebar_maincontent_boxes {
    margin:0 0px 20px 20px;
    padding:0 0 5px 0;
    width:718px;
    background: #dedede;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    float:left;
}
#two_column_left_sidebar_boxes {
    width:210px;
    margin:0px 0 20px 0px;
    min-height:360px;
    float:left;
    padding:0;
}
#two_column_left_sidebar_boxes .sidebarBox {
    margin:0px 0 22px 0;
    background: #dedede;
    padding:4px 10px 10px 10px;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    border-bottom:1px solid #cccccc;
    border-right:1px solid #cccccc;
}
#two_column_left_sidebar_boxes .sidebarBox h3 {
    padding:0 0 5px 0;
    font-size:1.25em;
    line-height:1.2em;
    color:#0054A7;
}

.contentWrapper {
    background:white;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    padding:10px;
    margin:0 10px 10px 10px;
}
span.contentIntro p {
    margin:0 0 0 0;
}
.notitle {
    margin-top:10px;
}
label {
    font-weight: bold;
    color:#333333;
    font-size: 120%;
}
input {
    font: 120% Arial, Helvetica, sans-serif;
    padding: 5px;
    border: 1px solid #cccccc;
    color:#666666;
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
}
textarea {
    font: 120% Arial, Helvetica, sans-serif;
    border: solid 1px #cccccc;
    padding: 5px;
    color:#666666;
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
}
textarea:focus, input[type="text"]:focus {
    border: solid 1px #4690d6;
    background: #e4ecf5;
    color:#333333;
}
.submit_button {
    font: 12px/100% Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: #ffffff;
    background:#4690d6;
    border: 1px solid #4690d6;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
    width: auto;
    height: 25px;
    padding: 2px 6px 2px 6px;
    margin:10px 0 10px 0;
    cursor: pointer;
}
.submit_button:hover, input[type="submit"]:hover {
    background: #0054a7;
    border-color: #0054a7;
}

input[type="submit"] {
    font: 12px/100% Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: #ffffff;
    background:#4690d6;
    border: 1px solid #4690d6;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
    width: auto;
    height: 25px;
    padding: 2px 6px 2px 6px;
    margin:10px 0 10px 0;
    cursor: pointer;
}
.cancel_button {
    font: 12px/100% Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: #999999;
    background:#dddddd;
    border: 1px solid #999999;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
    width: auto;
    height: 25px;
    padding: 2px 6px 2px 6px;
    margin:10px 0 10px 10px;
    cursor: pointer;
}
.cancel_button:hover {
    background: #cccccc;
}

.input-text,
.input-tags,
.input-url,
.input-textarea {
    width:448px;
}

.input-textarea {
    height: 200px;
}


#login-box {
    margin:0 0 10px 0;
    padding:0 0 10px 0;
    background: #dedede;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    width:240px;
    text-align:left;
}
#login-box form {
    margin:0 10px 0 10px;
    padding:0 10px 4px 10px;
    background: white;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    width:200px;
}
#login-box h2 {
    color:#0054A7;
    font-size:1.35em;
    line-height:1.2em;
    margin:0 0 0 8px;
    padding:5px 5px 0 5px;
}
#login-box .login-textarea {
    width:178px;
}
#login-box label,
#register-box label {
    font-size: 1.2em;
    color:gray;
}
#login-box p.loginbox {
    margin:0;
}
#login-box input[type="text"],
#login-box input[type="password"],
#register-box input[type="text"],
#register-box input[type="password"] {
    margin:0 0 10px 0;
}
#register-box input[type="text"],
#register-box input[type="password"] {
    width:380px;
}
#login-box h2,
#login-box-openid h2,
#register-box h2,
#add-box h2,
#forgotten_box h2 {
    color:#0054A7;
    font-size:1.35em;
    line-height:1.2em;
    margin:0pt 0pt 5px;
}
#register-box {
    text-align:left;
    width:400px;
    padding:10px;
    background: #dedede;
    margin:0;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
}
#persistent_login 
{
}

#persistent_login label {
    font-size:1.0em;
    font-weight: normal;
}

.search_listing {
    display: block;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    background:white;
    margin:0 10px 5px 10px;
    padding:5px;
}
.search_listing_icon {
    float:left;
}
.search_listing_icon img {
    width: 40px;
}
.search_listing_icon .avatar_menu_button img {
    width: 15px;
}
.search_listing_info {
    margin-left: 50px;
    min-height: 40px;
}
/* IE 6 fix */
* html .search_listing_info {
    height:40px;
}
.search_listing_info p {
    margin:0 0 3px 0;
    line-height:1.2em;
}
.search_listing_info p.owner_timestamp {
    margin:0;
    padding:0;
    color:#666666;
    font-size: 90%;
}

#owner_block {
    padding:10px;
}
#owner_block_icon {
    float:left;
    margin:0 10px 0 0;
}
#owner_block_rss_feed,
#owner_block_odd_feed,
#owner_block_bookmark_this,
#owner_block_report_this {
    padding:5px 0 0 0;
}
#owner_block_report_this {
    padding-bottom:5px;
    border-bottom:1px solid #cccccc;
}
#owner_block_rss_feed a {
    font-size: 90%;
    color:#999999;
    padding:0 0 4px 20px;
    background: url(<?php echo $vars['url']; ?>_graphics/icon_rss.gif) no-repeat left top;
}
#owner_block_rss_feed a:hover {
    color: #0054a7;
}
#owner_block_desc {
    padding:4px 0 4px 0;
    margin:0 0 0 0;
    line-height: 1.2em;
    border-bottom:1px solid #cccccc;
    color:#666666;
}
#owner_block_content {
    margin:0 0 4px 0;
    padding:3px 0 0 0;
    min-height:35px;
    font-weight: bold;
}
#owner_block_content a {
    line-height: 1em;
}
.ownerblockline {
    padding:0;
    margin:0;
    border-bottom:1px solid #cccccc;
    height:1px;
}
#owner_block_submenu {
    margin:20px 0 20px 0;
    padding: 0;
    width:100%;
}
#owner_block_submenu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
#owner_block_submenu ul li.selected a {
    background: #4690d6;
    color:white;
}
#owner_block_submenu ul li.selected a:hover {
    background: #4690d6;
    color:white;
}
#owner_block_submenu ul li a {
    text-decoration: none;
    display: block;
    margin: 2px 0 0 0;
    color:#4690d6;
    padding:4px 6px 4px 10px;
    font-weight: bold;
    line-height: 1.1em;
    -webkit-border-radius: 10px; 
    -moz-border-radius: 10px;
}
#owner_block_submenu ul li a:hover {
    color:white;
    background: #0054a7;
}

/* IE 6 + 7 menu arrow position fix */
* html #owner_block_submenu ul li.selected a {
    background-position: left 10px;
}
*:first-child+html #owner_block_submenu ul li.selected a {
    background-position: left 8px;
}

#owner_block_submenu .submenu_group {
    border-bottom: 1px solid #cccccc;
    margin:10px 0 0 0;
    padding-bottom: 10px;
}

#owner_block_submenu .submenu_group .submenu_group_filter ul li a,
#owner_block_submenu .submenu_group .submenu_group_filetypes ul li a {
    color:#666666;
}
#owner_block_submenu .submenu_group .submenu_group_filter ul li.selected a,
#owner_block_submenu .submenu_group .submenu_group_filetypes ul li.selected a {
    background:#999999;
    color:white;
}
#owner_block_submenu .submenu_group .submenu_group_filter ul li a:hover,
#owner_block_submenu .submenu_group .submenu_group_filetypes ul li a:hover {
    color:white;
    background: #999999;
}


/* ***************************************
    PAGINATION
*************************************** */
.pagination {
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    background:white;
    margin:5px 10px 5px 10px;
    padding:5px;
}
.pagination .pagination_number {
    display:block;
    float:left;
    background:#ffffff;
    border:1px solid #4690d6;
    text-align: center;
    color:#4690d6;
    font-size: 12px;
    font-weight: normal;
    margin:0 6px 0 0;
    padding:0px 4px;
    cursor: pointer;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
}
.pagination .pagination_number:hover {
    background:#4690d6;
    color:white;
    text-decoration: none;
}
.pagination .pagination_more {
    display:block;
    float:left;
    background:#ffffff;
    border:1px solid #ffffff;
    text-align: center;
    color:#4690d6;
    font-size: 12px;
    font-weight: normal;
    margin:0 6px 0 0;
    padding:0px 4px;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
}
.pagination .pagination_previous,
.pagination .pagination_next {
    display:block;
    float:left;
    border:1px solid #4690d6;
    color:#4690d6;
    text-align: center;
    font-size: 12px;
    font-weight: normal;
    margin:0 6px 0 0;
    padding:0px 4px;
    cursor: pointer;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
}
.pagination .pagination_previous:hover,
.pagination .pagination_next:hover {
    background:#4690d6;
    color:white;
    text-decoration: none;
}
.pagination .pagination_currentpage {
    display:block;
    float:left;
    background:#4690d6;
    border:1px solid #4690d6;
    text-align: center;
    color:white;
    font-size: 12px;
    font-weight: bold;
    margin:0 6px 0 0;
    padding:0px 4px;
    cursor: pointer;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
}


/* ***************************************
    MISC.
*************************************** */
/* general page titles in main content area */
#content_area_user_title h2 {   
    margin:0 0 0 8px;
    padding:5px;
    color:#0054A7;
    font-size:1.35em;
    line-height:1.2em;
}
/* reusable generic collapsible box */
.collapsible_box {
    background:#dedede;
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    padding:5px 10px 5px 10px;
    margin:4px 0 4px 0;
    display:none;
}   
a.collapsibleboxlink {
    cursor:pointer;
}

/* tag icon */  
.object_tag_string {
    background: url(<?php echo $vars['url']; ?>_graphics/icon_tag.gif) no-repeat left 2px;
    padding:0 0 0 14px;
    margin:0;
}   

/* profile picture upload n crop page */    
#profile_picture_form {
    height:145px;
}   
#current_user_avatar {
    float:left;
    width:160px;
    height:130px;
    border-right:1px solid #cccccc;
    margin:0 20px 0 0;
}   
#profile_picture_croppingtool {
    border-top: 1px solid #cccccc;
    margin:20px 0 0 0;
    padding:10px 0 0 0;
}   
#profile_picture_croppingtool #user_avatar {
    float: left;
    margin-right: 20px;
}   
#profile_picture_croppingtool #applycropping {

}
#profile_picture_croppingtool #user_avatar_preview {
    float: left;
    position: relative;
    overflow: hidden;
    width: 100px;
    height: 100px;
}   


/* ***************************************
    SETTINGS & ADMIN
*************************************** */
.admin_statistics,
.admin_users_online,
.usersettings_statistics,
.admin_adduser_link,
#add-box,
#search-box,
#logbrowser_search_area {
    -webkit-border-radius: 8px; 
    -moz-border-radius: 8px;
    background:white;
    margin:0 10px 10px 10px;
    padding:10px;
}

.usersettings_statistics h3,
.admin_statistics h3,
.admin_users_online h3,
.user_settings h3,
.notification_methods h3 {
    background:#e4e4e4;
    color:#333333;
    font-size:1.1em;
    line-height:1em;
    margin:0 0 10px 0;
    padding:5px;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;    
}
h3.settings {
    background:#e4e4e4;
    color:#333333;
    font-size:1.1em;
    line-height:1em;
    margin:10px 0 4px 0;
    padding:5px;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
}
.admin_users_online .profile_status {
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;
    background:#bbdaf7;
    line-height:1.2em;
    padding:2px 4px;
}
.admin_users_online .profile_status span {
    font-size:90%;
    color:#666666;
}
.admin_users_online  p.owner_timestamp {
    padding-left:3px;
}


.admin_debug label,
.admin_usage label {
    color:#333333;
    font-size:100%;
    font-weight:normal;
}

.admin_usage {
    border-bottom:1px solid #cccccc;
    padding:0 0 20px 0;
}
.usersettings_statistics .odd,
.admin_statistics .odd {

}
.usersettings_statistics .even,
.admin_statistics .even {

}
.usersettings_statistics td,
.admin_statistics td {
    padding:2px 4px 2px 4px;
    border-bottom:1px solid #cccccc;
}
.usersettings_statistics td.column_one,
.admin_statistics td.column_one {
    width:200px;
}
.usersettings_statistics table,
.admin_statistics table {
    width:100%;
}
.usersettings_statistics table,
.admin_statistics table {
    border-top:1px solid #cccccc;
}
.usersettings_statistics table tr:hover,
.admin_statistics table tr:hover {
    background: #E4E4E4;
}
.admin_users_online .search_listing {
    margin:0 0 5px 0;
    padding:5px;
    border:2px solid #cccccc;
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
}

p.longtext_editarea {
    margin:0 !important;
}


#topbar
{
    width:100%;
    /* height:48px; */
    background:#1d1d1d url("<?php echo $graphicsDir; ?>/topgradient.gif?v5") repeat-x left -1px;
}

#topRight
{
    position:absolute;
    right:0px;
    top:0px;
}

.topbarTable
{
    width:100%;    
    border-collapse:collapse;
}

#topbar form
{
    display:inline;
}

.topbarLinks a
{
    display:block;
    float:left;
    padding:14px 20px 10px 20px;
    border-left:1px solid #5d5d5d;
    border-right:1px solid #2f2f2f;
    height:23px;
    color:#e6e6e6;
}

.topbarLinks a:hover
{
    background:#1d1d1d url("<?php echo $graphicsDir; ?>/topgradient.gif?v5") repeat-x left -49px;  
    color:#e6e6e6;
    text-decoration:none;
}

.topbarLinks a#logoContainer
{
    padding:8px 22px 16px 15px;
    overflow:hidden;
    border-left:0px;
}

.topbarLinks form
{    
    padding-left: 10px;
    padding-right: 10px;
}

#loginButton, #loggedinArea
{    
    width:166px;
    display:block;
}

#loginButton
{
    height:47px;
}

#loginButton
{
    background:#4d4d4d url(<?php echo $graphicsDir; ?>/loginbutton_sm.gif) no-repeat left top;
}

a#loginButton:hover
{
    background-position:left -46px;
}

#loggedinArea
{
    background:url(<?php echo $graphicsDir; ?>/loggedinarea_rounded.gif?v2) no-repeat left top;
}    

a#loginButton:hover 
{
    text-decoration:none;
}

a#loginButton:hover .loginContent span
{
    text-decoration:underline;
}    

#loginButton img
{
    margin-right:10px;
    vertical-align:-4px;
}

#loginButton .loginContent
{
    display:block;    
    padding-top:12px;
    text-align:center;
    color:#e6e6e6;
    font-weight:bold;
}

.loggedInAreaContent
{
    display:block;    
    height:30px;
    padding:10px 0px 6px 0px;
    text-align:center;
    color:#e6e6e6;
    font-weight:bold;
}

.loggedInAreaContent a
{
    margin-left:5px;
    margin-right:5px;
}

.loggedInAreaContent a:hover
{
    border-bottom:1px solid black;
}

.dropdown
{
    position:absolute;
    z-index:10;
    left:100px;
    top:100px;
    width:180px;
    background-color:#2b2b2b;
    border:1px solid #b8b8b8;
    padding-bottom:8px;
    -moz-border-radius: 8px;
    -webkit-border-radius: 8px;
    display:none;
}

.dropdown_title
{
    padding:6px;
    font-weight:bold;
    border-bottom:1px solid #545454;
    color:#e6e6e6;
}

.dropdown_item
{
    display:block;
    color:black;
}

.dropdown_item_selected
{
    font-weight:bold;
}

a.dropdown_item:hover
{
    color:black;    
}

.dropdown_content
{   
    background-color:#e6e6e6;
    padding:3px;
}

#thin_column
{
    width:493px;
    margin:0 auto;   
}

#site_menu
{
    clear:both;
    padding-top:8px;
    text-align:center;
}

#edit_pages_menu
{
   text-align:center;
}

#site_menu a, #edit_pages_menu a
{
    margin:0px 3px;
    white-space:nowrap;
}

#site_menu a.selected
{
    font-weight:bold;
    color:black;
}

#edit_submenu
{
    text-align:center;
    height:20px;
    padding:3px 10px;
}

#edit_submenu a
{   
    color:white;
    font-weight:bold;
}


.float_right
{
    clear:both;
    display:block;
    float:right;
}

#heading
{    
    padding:15px;
    color:black;    
    letter-spacing:1px;
    font-family:"Gill Sans MT", sans-serif;
}

#heading img
{
    float:left;
    padding-right:10px;
}

#heading h1
{
    color:#222;
    font-size:22px;
    padding-top:5px;
    padding-bottom:0px;
    margin:0px;
}

#heading h1.withicon
{
    padding-top:20px;
}


#heading h1.withouticon,
#heading h2.withouticon
{
    text-align:center;
}

#heading h2
{
    color:#222;
    font-size:14px;
    padding:0px;
    margin:0px;
}

#content
{
    clear:both;
}

#content_top
{
    height:24px;
    background:#fff url("<?php echo $graphicsDir; ?>/contenttop.gif") no-repeat left top;  
}

#content_bottom
{
    height:24px;
    background:#fff url("<?php echo $graphicsDir; ?>/contentbottom.gif") no-repeat left top;  
}

#content_mid
{
    background:#fff url("<?php echo $graphicsDir; ?>/contentgradient.gif") repeat-y left top;  
    padding:0px 6px;
}

.section_header
{
    clear:both;
    background:#e6e6e6 url("<?php echo $graphicsDir; ?>/sectionheader.gif") no-repeat left top;  
    height:21px;
    padding:10px 15px;
    font-family:"Gill Sans MT", sans-serif;
    text-transform:uppercase;
    font-weight:bold;
    font-size:14px;
}

.section_content
{
    padding:5px 10px;
}

.sidebar_link
{
    position:absolute;
    left:490px;
}

.good_messages, .bad_messages 
{
    background:#ccffcc;
    color:#000000;
    padding:3px;
    width:483px;
    margin:3px auto;
    -webkit-border-radius: 4px; 
    -moz-border-radius: 4px;    
    border:2px solid #00CC00;
}

.good_messages p, .bad_messages p
{
    margin:4px;   
}

.bad_messages
{
    border-color:#CC0000;
    background:#ffcccc;
}

body
{
    background-color:#e7e2d7;
}

.padded
{
    padding:5px 10px;
}

.blog_post
{
    clear:both;
}

.blog_post_wrapper
{    
    border-bottom:1px solid #ddd;
    padding:8px;
}

.feed_org_icon
{
    float:left;
    width:50px;
}

.feed_org_icon img
{
    width:40px;
}

.feed_content
{
    float:left;
    width:410px;
}

.blog_date
{
    color: #aaa;
    font-size:11px;
}

.blog_more
{    
    float:right;
}

.transContributeLink
{
    display:block;
    float:right;
    font-size:9px;
}

.smallBlogImageLink
{
    float:left;
    margin-right:4px;
    margin-bottom:4px;    
    border: 1px solid #f0f0f0;

}

a.smallBlogImageLink:hover
{
    border: 1px solid #7391a9;
}

.largeBlogImageLink
{
    margin-bottom:10px;
    text-align:center;
    display:block;
}

#blogTimeline
{
    margin:5px 0px 5px 0px;
    position:relative;
    width:460px;
    height:50px;
}

#blogTimelineLeft, #blogTimelineRight, #blogTimelineLine
{
    position:absolute;    
    top:7px;
    height:26px;
}

#blogTimelineLeft
{
    left:18px;   
    width:13px;
    background:url(<?php echo $graphicsDir ?>/timeline.gif) no-repeat left -26px;
}

#blogTimelineRight
{
    left:431px;   
    width:13px;
    background:url(<?php echo $graphicsDir ?>/timeline.gif) no-repeat left -52px;
}

#blogTimelineLine
{
    left:31px;
    width:400px;
    background:url(<?php echo $graphicsDir ?>/timeline.gif) repeat-x left top;
}

.timelineMarker
{
    position:absolute;
    height:4px;
    top:27px;
    width:1px;
    overflow:hidden;
    background-color:#333;
}

.timelineLink
{
    position:absolute;    
    top:13px;
    width:5px;
    height:14px;
    display:block;
    overflow:hidden;
    background-color:#333;
}

.timelineCur
{
    position:absolute;
    height:17px;
    top:-3px;
    width:13px;
    background:url(<?php echo $graphicsDir; ?>/timeline.gif) no-repeat left -87px;
}

.timelineLabel
{
    position:absolute;
    top:30px;
    width:70px;
    text-align:center;
    font-size:10px;
}

#hoverPost
{
    position:absolute;
    top:27px;
}

#hoverPost img
{
    display:block;
    margin:0 auto;
}

#blogNavPrev, #blogNavNext
{
    position:absolute;
    display:block;
    height:19px;
    width:22px;
    top:9px;
}

#blogNavPrev
{
    left:0px;
    background: url(<?php echo $graphicsDir ?>/arrows_sm.gif) no-repeat left top;
}

#blogNavNext
{
    left:440px;
    background: url(<?php echo $graphicsDir ?>/arrows_sm.gif) no-repeat right top;
}

.homeLanguages
{
    text-align: center;
}

.homeHeading
{
    padding:5px 10px 10px 10px;    
    font:16px Arial;
}

.homeSection
{
    padding:5px;
    clear:both;
}

.homeSectionIcon
{
    float:left;
    margin-right:10px;
    border:1px solid #666699;
}

.homeSubheading
{
    font:bold 15px Arial;
}

.instructions
{
    padding:5px 0px;
}

.searchForm select
{
    font-size:11px;
}

.searchField
{
    width:220px;
}

.optionLabel
{
    font-weight:normal;
    font-size:100%;
}

.help
{
    color:#666;
    font-style:italic;
}

.tabs
{
    width:100%;    
    margin-bottom:10px;
    border-collapse:collapse;
}

.tab
{
    height:35px;
    background:url(<?php echo $graphicsDir ?>/tabs.gif) repeat-x left -35px;
    text-align:center;
    border-left:1px solid #ccc;
    border-right:1px solid #bbb;
}

.tab span
{
    display:block;
    padding:8px;
    color:#333;
}

.tabs .active
{
    background:url(<?php echo $graphicsDir ?>/tabs.gif) repeat-x left top;
    font-weight:bold;
}


.input
{
    padding:8px 0px;
}

.websiteUrl
{
    color:green;
    white-space:nowrap;
}

.widget_disabled
{
    color:gray;
}

#widget_delete
{
    float:right;
    background-color:#990000;
    border-color:#660000;
}

#widget_delete:hover
{
    background-color:#aa0000;
}

.optionLabelInline
{
    padding-right:10px;
}

.widget_image_top
{
    display:block;
    margin:0px auto 6px auto;
}

.widget_image_bottom
{
    display:block;
    margin:6px auto 0px auto;
}

.widget_image_left
{
    float:left;
    margin:0px 6px 6px 0px;
}

.widget_image_right
{
    float:right;
    margin:0px 0px 6px 6px;
}

.editor
{
    background-color:#4c4c4c;
}

.editor #heading h1
{
    color:#e6e6e6;
}

.editor #content_mid
{
    background:url("<?php echo $graphicsDir; ?>/editgradient.gif") repeat-y left top;  
}

.editor #content_top
{
    height:9px;    
    overflow:hidden;
    background:url("<?php echo $graphicsDir; ?>/edittop.gif") no-repeat left top;  
}

.editor #content_bottom
{
    background:#fff url("<?php echo $graphicsDir; ?>/editgradient.gif") repeat-y left top;  
}

.adminBox
{
    position:absolute;
    top:75px;
    right:2px;
    border:1px solid red;
    background:#ffcccc;
    padding:5px;
}

.adminBox a
{
    display:block;
    color:#000066;
}

#translate_bar
{
    padding: 5px 2px 5px 36px;
    height:32px;
    background:#fdfdfd url(<?php echo $graphicsDir; ?>/world.gif) no-repeat 5px 8px;
    border-bottom:1px solid #ccc;
    font-size:11px;
}
#translate_bar a
{
    white-space:nowrap;
}

.addUpdateButton
{
    float:right;
    margin:4px 0px !important;
}

#attachImage
{
    padding:4px;
    margin-top:2px;
    border:1px solid #ccc;
    -moz-border-radius:6px;
    -webkit-border-radius:6px;
    width:320px;
}

#attachControls
{
    padding:4px;
}

#attachImage input
{
    margin:3px;
}

.attachImageClose
{
    float:right;
    margin-left:8px;
    display:block;
    height:14px;
    width:14px;
    background:url(<?php echo $graphicsDir ?>/icon_customise_remove.png) no-repeat left top;
}

a.attachImageClose:hover
{
    background-position:left -16px;
}

.blogView
{
    float:right;
    margin-right:10px;
}

.gridTable 
{
    width:100%;
    border-collapse:collapse;
}

.gridTable td
{
    border:1px solid #ccc;
    padding:3px;
}

.dashboard_img_link
{
    width:27px;
    height:26px;
    text-align:center;
    float:left;
    clear:left;    
    margin-right:5px;
    background:url(<?php echo $graphicsDir; ?>/loggedinarea_rounded.gif?v2) no-repeat -20px -20px;
}

.dashboard_img_link_r
{
    width:31px;
    height:31px;
    float:left;
    clear:left;    
    margin-right:5px;
}
.dashboard_img_link_r img
{
    width:30px;
    height:30px;
}

.icon_with_bg
{
    padding:4px;
    vertical-align:middle;
    background:url(<?php echo $graphicsDir; ?>/loggedinarea_rounded.gif?v2) no-repeat -20px -20px;
}

.dashboard_text_link
{
    display:block;
    padding-top:3px
}

.dashboard_links div
{
    clear:both;
    padding-top:2px;
}

.input-checkboxes, .input-radio
{
    border:0px;
}

.language
{
    text-align:center;
    padding:8px 10px;
}

.commBox
{
    text-align: center;
    height:36px;
    color:white;
    width:100%;
}

.commBox a
{
    font-weight: bold;
    color:white;
}

.commBoxLeft
{
    background:url(<?php echo $graphicsDir ?>/commBox.gif) no-repeat right -10px;   
    width:45%;
}

.commBoxMain
{
    background:url(<?php echo $graphicsDir ?>/commBox.gif) repeat-x left -56px;   
    white-space:nowrap;
    padding:5px;    
}

.commBoxRight
{
    background:url(<?php echo $graphicsDir ?>/commBox.gif) no-repeat left -102px;   
    width:45%;
}
