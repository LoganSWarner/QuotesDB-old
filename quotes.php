<!doctype html>

<!-- Authors: Cade Foster and Logan Warner
     Description: main page for searching quote database. Uses javascript in
     quotes.js for database results via AJAX
-->


<html>
<head>
<style type="text/css" media="screen">
  suggest_link{
    background-color: #FFFFFF;
    padding: 2px 6px 2px 6px;
  }

  suggest_link_over{
    background-color: #5588EE;
    padding: 2px 6px 2px 6px;
  }

  .ui-autocomplete{
    background-color: #FFFFFF;
    max-width:200px;!important;
    border-style:solid;
    border-width:1px;
    position:fixed;
  }
  .ui-helper-hidden-accessible{
    display:none;
  }
  ul.ui-autocomplete {
    list-style: none;
    list-style-type: none;
    padding: 0px;
    margin: 0px;
  }
  
  .ui-state-focus{
    color:White;
    background:#0000ff;
    outline:none;
  }
  
  blockquote.style1{
    font: 16px/20px italic Times, serif;
    padding: 8px;
    background-color: #faebbc;
    border: 1px solid #e1cc89;
    margin: 12px;
    background-image: url(openquote1.gif);
    background-position: top left;
    background-repeat: no-repeat;
    text-indent: 23px;
  }
  blockquote.style1 span{
    display: block;
    background-image: url(closequote1.gif);
    background-repeat: no-repeat;
    background-position: bottom right;
  }
</style>

<script type="text/javascript"
  src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js">
</script>
<script type="text/javascript"
  src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js">
</script>
<script type="text/javascript"
  src="quotes.js">
</script>

</head>

<?php
  session_start();
?>

  <body onLoad="javascript:load('<?php echo session_id(); ?>')">
  
  <h2>Quote search'o'matic</h2>
  
  <hr/>
  <table>
    <tr>
      <td width="300" valign="top">
        <form id="mainForm" action="javascript:performSearch('<?php 
          echo session_id(); ?>')">
          <input size="30" type="text" id="search" 
            onKeyUp="javascript:suggest('<?php 
            echo session_id(); 
            ?>');" autocomplete="off"/>
          <select id="numResults">
            <option name="1">
              1
            </option>
            <option name="2">
              2
            </option>
            <option name="3">
              3
            </option>
            <option name="4">
              4
            </option>
            <option name="5">
              5
            </option>
            <option name="6">
              6
            </option>
            <option name="7">
              7
            </option>
            <option name="8">
              8
            </option>
            <option name="9">
              9
            </option>
            <option name="10">
              10
            </option>
            <option name="11">
              11
            </option>
            <option name="12">
              12
            </option>
            <option name="13">
              13
            </option>
            <option name="14">
              14
            </option>
            <option name="15">
              15
            </option>
          </select>
          <input type="submit" value="Go"/>
          <div id="search_suggest"></div>
        </form>
      </td>
      <td width="10"></td>
      <td valign="top">
        <b>Most popular searches:</b><br/>
        <table id="popSearches">
          <!-- set by javascript -->
        </table>
      </td>
      
      <td width="10"></td>
      <td  valign="top">
        <b>Recent searches:</b><br/>
        <table id="recentSearches">
          <!--  set by javascript -->
        </table>
      </td>
      <td>
        <select id="numSearches">
          <option name="1">
              1
            </option>
            <option name="2">
              2
            </option>
            <option name="3">
              3
            </option>
            <option name="4">
              4
            </option>
            <option name="5">
              5
            </option>
        </select>
      </td>
    </tr>
  </table>

  <hr/>
  <span id="quotesArea">
    <!--  filled by javascript -->
  </span>
  <hr/>
  <a href="admin.php">Admin page</a>

</body>
</html>
