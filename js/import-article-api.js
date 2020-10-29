var jq2 = jQuery.noConflict();
jq2(function( $ ) {
  jq2("#date_start" ).datepicker(
    { dateFormat: 'yy-mm-dd' }
  ).datepicker("setDate",'now');
  jq2("#date_end" ).datepicker(
    { dateFormat: 'yy-mm-dd' }
  ).datepicker("setDate",'now');
  
  jq2("#search_note").button();
   // $("#note_agencie").selectmenu();
   // $("#note_category").selectmenu();
   show_article();
});

function confirm_save_note(id){
  jq2( "#dialog-confirm" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: false,
      buttons: {
        Yes: function() {
          save_note(id)
          jq2( this ).dialog( "close" );
        },
        Cancel: function() {
          jq2( this ).dialog( "close" );
        }
      }
    });
}
 function save_note(id){
  let data = {}
  data.title = jq2(`#ap_title_${id}`).html();
  data.source = jq2(`#source_${id}`).val();
  data.date = jq2(`#date_${id}`).val();
  data.category =jq2(`#category_${id}`).val();
  data.content =jq2(`#conten_${id}`).html();
  jq2.ajax({
			url : ajaxurl,
			type: 'post',
			data: {
				action : 'import_note_from_api',
				datos: data
      } ,
      beforeSend: function(){
        jq2(`#loading_${id}`).show();
        jq2(`#loading_${id}`).html(" Por favor espere .... <img src = './images/loading.gif' >");
			},
    success: function(resultado){
      jq2(`#loading_${id}`).hide();
      jq2(`#loading_${id}`).html("");
      window.open(resultado, '_blank' );
      //console.log(resultado);
      //jq2(location).attr('href',resultado).attr('target','_blank');
    }
 });
}

async function search_notes(){
   let date_start =  jq2("#date_start").val();
   let date_end = jq2("#date_end").val();
   let note_title = jq2("#note_title").val();
   let note_category = jq2("#note_category").val();
   let note_agencie = jq2("#note_agencie").val();
   let note_items = jq2("#note_items").val();
   jq2("#content-list-notes").html(``);  
   let filter = {};
  if(date_start != ""){
    filter.inputDateS = date_start;
      if(date_end != ""){
        filter.inputDateE = date_end;
      }else{
        alert("Indique el rango de fecha");
        jq2("#date_end").focus();
       
      }
   }

   if(note_items != ""){
    filter.items = parseInt (note_items);
    }
   if(note_title != ""){
     filter.inputTitle = note_title;
   }
   if(note_category != ""){
     filter.inputCategory = note_category;
   }
   if(note_agencie != ""){
    filter.inputSource= note_agencie;
   }

console.log(filter);
    let urlApi = 'https://hubcontent.televisa.xyz/api';
    //let urlApi = 'http://localhost:3000/api';
    let title = "covid 19";
    let query = `query buscaArticle($inputTitle: String , $inputCategory : String, $inputDateS :  Date , $inputDateE : Date ,$items : Int,  $inputSource : String) {
    searchArticles(inputTitle :$inputTitle, inputCategory : $inputCategory , inputDateS : $inputDateS , inputDateE : $inputDateE ,items : $items, inputSource : $inputSource ){
      _id
      title
      category
      publishDate
      source
      content
    }
}`;

let opc = {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    query,
    variables:  filter 
  })
}

let response = await fetch(urlApi, opc)
let json = await response.json()
let article = json.data.searchArticles
return article
}

function show_article(){
  search_notes().then(article => { 
    let totalElem = article.length;
  if(totalElem>0){
      jq2("#content-list-notes").html(`<div id="accordion"></div>`);  
      let num =1;
    for(let i in article){
        jq2("#accordion").append(`<h3>
              <div class="hd_note">
                  <a href = "#" onClick = "confirm_save_note(${i});"> &nbsp;&nbsp; | &nbsp;&nbsp; IMPORTAR  &nbsp;&nbsp; | </a>
                  &nbsp;&nbsp;  ${article[i].publishDate} &nbsp;&nbsp;| &nbsp;&nbsp;${article[i].category} &nbsp;&nbsp;| &nbsp;&nbsp;${article[i].source} 
                </div> 
            <div class="lb_list_num">${num}.- </div> <div  id="ap_title_${i}"> ${article[i].title}</div>
              </h3>
              <div><div class ="lavelLoading" id="loading_${i}"></div>
                  <div id="conten_${i}" > ${article[i].content}</div>
              <input type = "hidden" id ="category_${i}" value = "${article[i].category}" >   
              <input type = "hidden" id ="source_${i}" value = "${article[i].source}" > 
              <input type = "hidden" id ="date_${i}" value = "${article[i].publishDate}" >   
              <input type = "hidden" id ="id_monog_${i}" value = "${article[i]._id}" >   
              </div>`);
        num++;
      }
      jq2("#accordion").accordion( { heightStyle: "content" });
  }else {
    jq2("#content-list-notes").html(`<div id="noElements">NO SE ENCONTRARON ELEMENTOS CON ESTOS CRITERIOS :(</div>`);  
  }
});
}
