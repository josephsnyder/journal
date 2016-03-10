// When page ready
var selectIssue = false;
var resizeEvent;

$(document).ready(function(){
  $('.issueTitle').each(function(){
    $(this).dotdotdot({
    callback	: function( isTruncated, orgContent ) {
      if(isTruncated)
        {
        $(this).qtip({
            content: {
                attr: 'qtipconditionnal'
            }
        });
        }
    }}
    );
 });


  resizeEvent = function(){
    $.each($('.SearchResultEntry'),function(){
      $(this).find('.ResultTitle').dotdotdot( {'height': 20});
      $(this).find('.ResultDescription').trigger("update");
    })

  }

  $(window).resize(resizeEvent);

  $('.issuePage').fancybox({type: 'ajax'});


  // Create the root html element of each tree
  $.each(json.trees, function(key, tree)
    {
    $('#treeWrapper').append('<div class="TreeEntry"><img class="tooggleButton" src="'+json.global.webroot+'/privateModules/journal/public/images/arrow-bottom.png"/><h4>'+tree.title+' </h4><div id="categoryTree-'+tree.key+'" class="categoryTree"></div>');
    /* Init trees */
    $("div.categoryTree:last").dynatree({
      debugLevel : 0,
      checkbox: true,
      selectMode: 3,
      children: tree.children,
      onSelect: function(select, node) {
        $('#infoElement').hide();
        searchDatabase(false);
      },
      onDblClick: function(node, event) {
        node.toggleExpand();
      },

        cookieId: "dynatreeEdit-"+key,
        idPrefix: "dynatreeEdit-"+key
      });

    if(tree.children.length > 5)
      {
      $("div.categoryTree:last").hide();
      $("div.categoryTree:last").parent().find('img.tooggleButton').attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-right.png');
      }
    }
  );

  $('.issueButton .issueTitle, .issueButton .issueSubTitle').click(function(){
    var container = $(this).parents('.issueButton');
    var key = parseInt(container.attr('key'));
    $('.issueSelected').each(function(){
      $(this).removeClass('issueSelected');
      $(this).find('.issueDetails').hide();
    })
    if(selectIssue == key || key === -999) selectIssue = false;
    else
      {
      container.addClass('issueSelected');
      container.find('.issueDetails').show();
      selectIssue = key;
      }
    $('#infoElement').hide();
    searchDatabase(false);
  });
  if(json.selectedIssue != "")
    {
    selectIssue = parseInt(json.selectedIssue);
    $('.issueButton[key='+selectIssue+']').addClass('issueSelected');
    }


  // Init instant search
  $('#search_button').click(function(){
      $('#infoElement').hide();
      searchDatabase(false);
    });

  $('#clear_button').click(function(){
      $('#infoElement').hide();
      $('#live_search').val("")
      searchDatabase(false);
    });

  // Init tree toogle
  $('img.tooggleButton').click(function(){
    var tree = $(this).parent('div.TreeEntry').find('div.categoryTree');
    if(tree.is(':visible'))
      {
      $(this).attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-right.png');
      }
    else
      {
      $(this).attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-bottom.png');
      }
    tree.toggle();
  });

  searchDatabase(false)
})

function getSelectedCategories()
  {
  var result = new Array();
  $("#treeWrapper div.categoryTree").each(function(i, n){
    var selected =  new Array();
    var nodes = $(this).dynatree("getSelectedNodes");
    $.each(nodes, function(index, value){
      selected.push(value.data.key);
    });
    if (selected.length > 0) result.push(selected);
  });
  return result;
  }

//** Query the api */
function searchDatabase(append)
  {
  var fullQuery = "text-journal.enable:true ";

  var query = $('#live_search').val();
  if(query != "" && query!="Search...")
    {
    var vals = [];

    // Find all the "" pairs
    var re = /".*"/i;
    while (true)
      {
      var val = query.match(re);
      if (val == null)
        {
        break;
        }
      vals = vals.concat(val);
      query = query.replace(val, '');
      }

    vals = vals.concat(query.split(" "));

    // Remove any empty values
    vals = vals.filter(function(val){
      return val !== "";
    });

    // Re-construct query
    query = vals[0];
    for (i = 1; i < vals.length; i++)
      {
      query += " AND ";
      query += vals[i];
      }

    fullQuery += "AND (name:("+query+") OR description:("+query+") OR ngram_search:("+query+"))";
    }


  if(selectIssue)
    {
    fullQuery+= " AND (";
    fullQuery+= " text-journal.issue:"+selectIssue+" ";
    fullQuery+= ")";
    }
  if(json.selectedCommunity != "")
    {
    fullQuery+= " AND (";
    fullQuery+= " text-journal.community:"+json.selectedCommunity+" ";
    fullQuery+= ")";
    }
  var allQuery = '';
  var categories = getSelectedCategories();
  var certLevel =  [];
  if(categories.length != 0)
    {
    $.each(categories, function(idx, val){
      fullQuery+= " AND (";
      $.each(val, function(index, value){
        if(index != 0) fullQuery += " OR ";
        if(value.indexOf("certified") != -1)
          {
          fullQuery+= "text-journal.certification_level:"+value.charAt(value.length - 1)+" ";
          certLevel.push(value.charAt(value.length - 1));
          }
        else if(value.indexOf("submission_type") != -1)
          {
          fullQuery+= "text-journal.submission_type:"+value.charAt(value.length - 1)+" ";
          }
        else if(value.indexOf("code_in_flight") != -1)
          {
          fullQuery+= "name:\"Code in Flight\" ";
          }
        else if(value.indexOf("with_code") != -1)
          {
          fullQuery+= "text-journal.has_code:true ";
          }
        else if(value.indexOf("with_test_code") != -1)
          {
          fullQuery+= "text-journal.has_test_code:true ";
          }
        else if(value.indexOf("with_review") != -1)
          {
          fullQuery+= "text-journal.has_reviews:true ";
          }
        else
          {
          fullQuery+= " text-journal.categories:"+value+" ";
          }
      });
      fullQuery+= ")";
    });
    }
  allQuery= fullQuery.replace(/AND \(text-journal.cer[(text\-journal\.certification\_level:1-4OR ]+ \)/,"")
  $('img#searchLoadingImg').show();
  ajaxSearch(append,fullQuery,allQuery,certLevel);
}


function ajaxSearch(append,fullQuery,allQuery,certLevel) {

  var shown = $('.resourceLink').length;
  if(!append) shown = 0;
  ajaxWebApi.ajax({
        method: 'midas.journal.search',
        args: "offset="+shown+"&query="+fullQuery+"&level="+certLevel+"&secondQuery="+allQuery,
        log: true,
        success: function (retVal) {
          $('img#searchLoadingImg').hide();
          var total = 0;
          if(!append) $('.SearchResults').html("");
          if(!append) $('#noResultElement').show();
          $.each(retVal.data, function(index, value)
          {
          total = value.total;
          $('#noResultElement').hide();
          addAndFormatResult($('.SearchResults'), {'rating': value.rating, 'type': value.type,
            'id':value.revisionId, 'title': value.title, "logo": value.logo,
            'description': value.description, 'statistics': value.statistics,
            'authors': value.authors, 'isCertified' : value.isCertified, 'certifiedLevel': value.certifiedLevel,'pastCertificationRevisionNum': value.pastCertificationRevisionNum,
            'pastCertificationRevisionKey': value.pastCertificationRevisionKey})
          })
          var shown = $('.resourceLink').length;
          if(total > shown)
            {
            $('#showMoreResults').show();
            $('#showMoreResults a').unbind('click').click(function(){
              searchDatabase(true);
            })
            }
          else
            {
            $('#showMoreResults').hide();
            }

          if(total == "")
            {
            $('.SearchCount').hide();
            }
          else if(total > 1)
            {
            $('.SearchCount').show();
            $('.SearchCount').html(total+ " resources available.")
            }
          else
            {
            $('.SearchCount').show();
            $('.SearchCount').html(total+ " resource available.")
            }

          resizeEvent();
          setTimeout(resizeEvent, 200);
        },
        error: function (retVal) {
            midas.createNotice(retVal.message, 3000, 'error');
        },
        complete: function () {
        }
    });
}

/** Simple templating mechanism */
function addAndFormatResult(container, values) {
  if (container.html().indexOf(values['title']) == -1) {
    var str = document.getElementById('SearchResultTemplate').innerHTML;
      if(values.pastCertificationRevisionKey !== "")
        {
        values.id = values.pastCertificationRevisionKey;
        }
    $.each(values, function(key,value)
     {
     str = str.replace("{"+key+"}", value);
     str = str.replace("{"+key+"}", value);
     str = str.replace("{"+key+"}", value);
     }
    );
    container.append(str);
    var newElement = $('div.SearchResultEntry:last');
    newElement.find('.ResultTitle').dotdotdot( {'height': 20});

    if(values.logo == "")
      {
      newElement.find('.ResultLogo:first').remove();
      }
    else
      {
      newElement.find('.ResultLogo:last').remove();
      }

    if(values.isCertified == 0)
      {
      newElement.find('.CertifiedWrapper').remove();
      newElement.find('.CertifiedLevel').remove();
      }
    else
      {
      var revisionText = ''
      if(values.pastCertificationRevisionNum !== "")
        {
        revisionText = "Revision " + values.pastCertificationRevisionNum + ": ";
        }
      newElement.find('.CertifiedLevel').html(revisionText + "(Level "+values.certifiedLevel+")");
      }

    newElement.find('.ResultDescription').dotdotdot();
    return str;
  }
};
