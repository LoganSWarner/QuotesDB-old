<?php
/* Cade Foster and Logan Warner
   cjfoster and lswarner
   Various functions for our quote search application
*/

$Result = 'init'; // String to pass back to caller.

// Connect to the database
try{
  $dbh = new PDO("mysql:host=localhost;dbname=quotes", "web_quotes", "thisIsInsecure");
  //$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
  echo "ERROR: couldn't connect to the database.";
  exit();
}

// Check the GET params to see what function to call.
if (isset($_GET["getSearches"]))
{
  $Result = getSearches($dbh, $_GET["sessionID"]);
}

if (isset($_GET["search"]))
{
  $Result = getQuotes($dbh, $_GET["search"], $_GET["numResults"], $_GET["sessionID"]);
}

if (isset($_GET["rating"]))
  rateQuote($_GET["sessionID"], $_GET["quoteID"], $_GET["rating"]);

// delRatings, delSearch
//    if it's deleing searches or ratings, echo out "OK"

// Return two arrays: the most recent searches (globally), and the most popular searches (for that session id)
function getSearches($dbh, $sessionID)
{
  $res = array();
  try{
    $stmnt = $dbh->prepare(
      'SELECT searchText
      FROM searches
      ORDER BY searchTime
      DESC
      LIMIT 0, 5;');
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
    exit();
  }
  $stmnt->execute();
  $res = $stmnt->fetchAll(PDO::FETCH_ASSOC);
  $recentSearches = '"Recent": [ ';
  
  if ($res)
  {
    foreach ($res as $element)
      $recentSearches .= '"'.$element['searchText'].'", ';
    $recentSearches = substr($recentSearches, 0, -2);
  }
  $recentSearches .= ']';
  
  try{
    $stmnt = $dbh->prepare(
      'SELECT searchText
      FROM searches
      WHERE sessionID = ?
      ORDER BY amount
      DESC
      LIMIT 0, 5;');
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
    exit();
  }
  $stmnt->bindParam(1, $sessionID, PDO::PARAM_STR);
  $stmnt->execute();
  $res = $stmnt->fetchAll(PDO::FETCH_ASSOC);
  $popularSearches = '"Popular": [ ';

  if ($res)
  {
    foreach ($res as $element)
      $popularSearches .= '"'.$element['searchText'].'", ';
    $popularSearches = substr($popularSearches, 0, -2);
  }
  $popularSearches .= ']';

  return '{"Result" : {'.$recentSearches.', '.$popularSearches.'} }';
//	echo '{"Result": [ {"Recent": [ {"0":"one"}, {"1":"two"}, {"2":"three"}, {"3":"four"}, {"4":"five"} ] }, { "Popular": [ {"0":"six"}, {"1":"seven"}, {"2":"eight"}, {"3":"nine"}, {"4":"ten"} ]}}';
}//getSearches


// Return an array of quotes based on the given set of search word(s), limit to max total quotations
function getQuotes($dbh, $search, $max, $sessionID)
{
  $Result = 'top of getQuotes';

  // Update the table if this search has already been performed by this session id
  $res = array();
  try{
    $stmnt = $dbh->prepare(
      'SELECT id
      FROM searches
      WHERE searchText = ?
      AND sessionID = ?;');
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
    exit();
  }
  $stmnt->bindParam(1, $search, PDO::PARAM_STR);
  $stmnt->bindParam(2, $sessionID, PDO::PARAM_STR);
  $stmnt->execute();
  $res = $stmnt->fetchAll(PDO::FETCH_ASSOC);

  if ($res[0]["id"])
  {
    try{
     $stmnt = $dbh->prepare(
       'UPDATE searches
       SET amount = amount + 1
       WHERE id = ?;');
    }catch(PDOException $e){
      echo "ERROR: could not prepare statement.";
      exit();
    }
    $stmnt->bindParam(1, $res[0]['id'], PDO::PARAM_INT);
    $stmnt->execute();
  }//if

  // Otherwise insert a new search into the database
  else
  {
    try{
      $stmnt = $dbh->prepare(
        'INSERT INTO searches (sessionID, searchText, amount)
         VALUES (?, ?, 1);');
    }catch(PDOException $e){
      echo "ERROR: could not prepare statement.";
      exit();
    }
    $stmnt->bindParam(1, $sessionID, PDO::PARAM_STR);
    $stmnt->bindParam(2, $search, PDO::PARAM_STR);
    $stmnt->execute();
  }
  
  // Return the array of quotes matching this search
  $quoteResults = array();
  $quotedSearch = $dbh->quote("+".$search."*");
  try{
    $stmnt = $dbh->prepare(
      "SELECT *
      FROM quotes
      WHERE MATCH(author, quote, category)
      AGAINST($quotedSearch IN BOOLEAN MODE)
      LIMIT 0, ?;");
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
  exit();
  }
  $stmnt->bindParam(1, intval($max), PDO::PARAM_INT);
  $stmnt->execute();
  $quoteResults = $stmnt->fetchAll(PDO::FETCH_ASSOC);
  
  $Result = '[';

  $i = 0;
  foreach($quoteResults as $result){
    $Result .= "{\"quoteID\":\"{$result['id']}\", \"author\":";
    $Result .= "\"{$result['author']}\",\"quote\":\"{$result['quote']}\",";
    $Result .= "\"category\":\"{$result['category']}\"}";
    if($i != $max - 1)
      $Result .= ', ';
    $i++;
  }//for
  $Result .= ']';
  
  return $Result;
}//getQuotes

// Add/change rating for the quote identified by quoteID for user identified by sessionID
// score is the star rating: 1, 2, 3, 4 or 5.
function rateQuote($sessionID, $quoteID, $score)
{
  try{
    $stmnt = $dbh->prepare(
      'SELECT *
      FROM ratings
      WHERE quoteID = ?
      AND sessionID = ?;');
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
    //exit();
  }/*
  $stmnt->bindParam(1, $quoteID, PDO::PARAM_INT);
  $stmnt->bindParam(2, $sessionID, PDO::PARAM_STR);
  $stmnt->execute();
  $res = $stmnt->fetch();
  */
  if($score != "request")
  {
    if($res)
    {
      try{
        $stmnt = $dbh->prepare(
          'UPDATE ratings
          SET rating = ?
          WHERE quoteID = ?
          AND sessionID = ?;');
      }catch(PDOException $e){
        echo "ERROR: could not prepare statement.";
        exit();
      }
      $stmnt->bindParam(1, $score, PDO::PARAM_FLOAT);
      $stmnt->bindParam(2, $quoteID, PDO::PARAM_INT);
      $stmnt->bindParam(3, $sessionID, PDO::PARAM_STR);
      $stmnt->execute();
    }//if
    else
   {
     try{
       $stmnt = $dbh->prepare(
         'INSERT INTO ratings(rating, quoteID, sessionID)
         VALUES(?, ?, ?);');
     }catch(PDOException $e){
       echo "ERROR: could not prepare statement.";
       exit();
     }
     $stmnt->bindParam(1, $score, PDO::PARAM_FLOAT);
     $stmnt->bindParam(2, $quoteID, PDO::PARAM_INT);
     $stmnt->bindParam(3, $sessionID, PDO::PARAM_STR);
     $stmnt->execute();
    }
  }
    /*
  try{
    $stmnt = $dbh->prepare(
      'SELECT *
      FROM ratings
      WHERE quoteID = ?
      AND sessionID = ?;');
  }catch(PDOException $e){
    echo "ERROR: could not prepare statement.";
    exit();
  }
  $stmnt->bindParam(1, $quoteID, PDO::PARAM_INT);
  $stmnt->bindParam(2, $sessionID, PDO::PARAM_STR);
  $stmnt->execute();
  $res = $stmnt->fetch(PDO::FETCH_ASSOC);
  
  $Result = "{ 'avgRating : '".$res['avgRating'].", 'yourRating : '".
    $res['yourRating']. "}";*/
}

// Return the average score of this quote, "" if no score for the quote yet.
// If sessionID not "", we return current score set by this user.
function getQuoteScore($quoteID, $sessionID = "")
{
  
}

// Deletes searches from the database, if sessionID is set only delete entries for that sessionID
function deleteSearches($sessionID = "")
{
  
}

// Deletes ratings from the database, if sessionID is set only delete entries for that sessionID
function deleteRatings($sessionID = "")
{
  
}

echo $Result;
$dbh->close();
?>