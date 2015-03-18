<!doctype html>

<html>
<?php 
  session_start();
?>
<head>
<script type="text/javascript"
  src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js">
</script>
<script type="text/javascript">
  function delSearches(sessionID){
    $.get("quotesDB.php", { delSearch : 'yes', sessionID : sessionID }).always(
      function(data){
        $("#deletionResult").html('');
        $("#deletionResult").append('Deleted searches, sessionID = ' +
          sessionID + '<hr/>');
      });
  }//delSearches

  function delRatings(sessionID){
    $.get("quotesDB.php", { delRatings : 'yes', sessionID : sessionID }).always(
      function(data){
        $("#deletionResult").html('');
        $("#deletionResult").append('Deleted ratings, sessionID = ' +
          sessionID + '<hr/>');
      });
  }//delRatings
</script>
<?php
  if(isset($_POST['sessionID'])){
    if(isset($_POST['delSearches'])){
      ?>
      <script type="text/javascript">
        delSearches('<?php echo $_POST['sessionID']; ?>');
      </script>
      <?php
    }else{
      ?>
      <script type="text/javascript">
        delRatings('<?php echo $_POST['sessionID']; ?>');
      </script>
      <?php
    }//if else
  }//if
?>
</head>
<body>
  <h2>Quote search'o'matic admin</h2><hr/>
  <span id="deletionResult">
    <!-- set by javascript -->
  </span>
  <form name="delSearches" action="admin.php" method="POST">
    Session ID:
    <input type="text" size="35" 
      value="<?php echo session_id(); ?>" name="sessionID"/>
    <input type="submit" value="Delete Searches" name="delSearches"/>
  </form>
  <form name="delRatings" action="admin.php" method="POST">
    Session ID: 
    <input type="text" size="35" 
      value="<?php echo session_id(); ?>" name="sessionID"/>
    <input type="submit" value="Delete Ratings" name="delRatings"/>
  </form>
  <hr/>
  <a href="quotes.php">Search page</a>
</body>
</html>