/**
 * @author Logan Warner
 * @author Cade Foster
 * @description javascript to send AJAX requests for quotes.php to
 * get results from the database and update quotes.php
 */

//onLoad function
function load(sessionID){
  $.getJSON("quotesDB.php", { getSearches : 'yes', sessionID : sessionID,
    numResults : 5},
    function(data){
      handleSearches(data.response, sessionID);
    }).fail(function(data){handleSearches(data.responseJSON,
    sessionID);});
  return false;
}//load

//fills in search field before searching for the user
function setSearch(searchStr, sessionID){
  $("#search").val(searchStr);
  performSearch(sessionID);
}//setSearch

function performSearch(sessionID){
  $("#mainForm").focus();
  //do search
  var numResults = $("#numResults option:selected").attr('name');
  if(!numResults)
    numResults = 1;
  //normalize and trim ekcess whitespace
  searchString = $("#search").val().trim().toLowerCase()
    .replace(/[^a-z0-9' ]/g, '').replace(/\s{2,}/g, ' ');
  if(searchString.localeCompare("") != 0){
    $.getJSON("quotesDB.php", 
      { search : searchString, numResults : numResults, 
        sessionID : sessionID},
        function(data){
          handleSearching(data.responseJSON, sessionID);
        }).fail(
          function(data){
            handleSearching(data.responseJSON, sessionID);
        });
    //ask for recent and common searches to be populated
    numResults = $("#numSearches option:selected").attr('name');
    if(!numResults)
      numResults = 5;
    $.getJSON("quotesDB.php", { getSearches : 'yes', sessionID : sessionID,
      numResults : numResults},
      function(data){
        handleSearches(data.response, sessionID);
      }).fail(function(data){handleSearches(data.responseJSON, sessionID);});
  }//if string isn't empty
}//performSearch

function handleSearching(quoteResults, sessionID){
  //quoteResults is an array of results, where each result is a quote object 
  //(contains quoteID, author, quote, and category)
  $("#quotesArea").html('');//clear quotes
  var numResults = quoteResults.length;
  for(var i = 0; i < numResults; i++){
    //output quotes (remember nice formatting including &nbsp)
    $("#quotesArea").append(createQuote(quoteResults[i]));
    //ask for ratings (might take a bit, but can't happen before span is made)
    $.getJSON("quotesDB.php", {rating : "request", 
      quoteID : quoteResults[i].quoteID, sessionID : sessionID},
        function(data){
          handleRating(data.response, sessionID);
        }).fail(
        function(data){
          handleRating(data.responseJSON, sessionID);
        }
      );
  }//for number of results
}//handleSearching

//puts together the html for a single quote
function createQuote(quoteResult){
  var returnString = '<blockquote class="style1"><span>';
  returnString += quoteResult.quote;
  returnString += '<br/></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><i>-';
  returnString += quoteResult.author;
  returnString += '</i></b>&nbsp;&nbsp;(category: ';
  returnString += quoteResult.category;
  returnString += ')<br/><span id="ratingArea';
  returnString += quoteResult.quoteID; 
  return returnString += '"></span><br/></blockquote>';
}//createQuote

//puts together the html for a star rating
function createRating(numStars, isPersonal, quoteID, sessionID){
  var returnString = '&nbsp;&nbsp;&nbsp;&nbsp;';
  if(!isPersonal)
    returnString += 'Community rating: ';
  else
    returnString += 'Your rating: ';
  
  for(var i = 0; i < 5; i++){
    returnString += '<img src="';
    if(numStars > i){
      if(numStars > i+.5)
        returnString += 'star';
      else
        returnString += 'halfstar';
    }else
      returnString += 'nostar';
    returnString += '.png" alt="' + (i+1) + 'star"';
    
    if(isPersonal)
      returnString += ' onClick="javascript:ratingClicked(' + (i+1) + ', ' + 
      quoteID + ', \'' + sessionID + '\')"';
    returnString += ' width="24" height="24"/>';
    if(isPersonal)
      returnString += '&nbsp;';
  }//for
  
  return returnString;
}//createRating

function ratingClicked(starNum, quoteID, sessionID){
  $.getJSON("quotesDB.php", 
    {rating : starNum, quoteID : quoteID, sessionID : sessionID},
      function(data, status){
        handleRating(data.response, sessionID);
    }).fail(function(data){handleRating(data.responseJSON, sessionID);});
  
  return false;
}//ratingClicked

function handleRating(ratingObject, sessionID){
  var quoteID = ratingObject.quoteID;
  //clear rating area
  $("#ratingArea" + quoteID).html('');
  //display avgRating (note rating is in format of a number from 0 to 5 
  //in increments of .5)
  $("#ratingArea" + quoteID).append(createRating(ratingObject.avgRating, 
    false, quoteID, sessionID));
  //dispaly the user's current rating of this quote
  $("#ratingArea" + quoteID).append(createRating(ratingObject.yourRating, 
    true, quoteID, sessionID));
}//handleRatings

function handleSearches(searchesObject, sessionID){
  //clear both
  $("#popSearches").html('');
  $("#recentSearches").html('');
  
  var numRecSearches = searchesObject.Result.Recent.length;
  if(numRecSearches > 5)
    numRecSearches = 5;
  for(var i = 0; i < numRecSearches; i++){
    var curSearch = searchesObject.Result.Recent[i];
    $("#recentSearches").append('<tr><td>&nbsp;&nbsp;<a href="' + 
      'javascript:setSearch(\'' + curSearch + '\', \'' + 
      sessionID + '\');">' + curSearch + '</a></td></tr>');
  }//for
  var numPopSearches = searchesObject.Result.Popular.length;
  if(numPopSearches > 5)
    numPopSearches = 5;
  for(var i = 0; i < numPopSearches; i++){
    var curSearch = searchesObject.Result.Popular[i];
    $("#popSearches").append('<tr><td>&nbsp;&nbsp;<a href=' + 
      '"javascript:setSearch(\'' + curSearch + '\', \'' + 
      sessionID + '\');">' + curSearch + '</a></td></tr>');
  }//for
}//handleSearches

function suggest(sessionID){
  $.getJSON("quotesDB.php", { suggest : 'yes', typing : $("#search").val(), 
    sessionID : sessionID}, 
    function(data){
      handleSuggest(data.response);
    }).fail(function(data){handleSuggest(data.responseJSON);});
}//suggest

function handleSuggest(suggestList){
  $("#search").autocomplete({
    select:function(event, ui){
      performSearch(ui.item.value);
    },
    source:suggestList.suggestList
    });
}//handleSuggest