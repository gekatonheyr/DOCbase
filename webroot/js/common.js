function getAgrData(agr_id){
    url = '/ajax/pages/getAgrData/' + agr_id +'?type=table';
    $.ajax(url).done(function(data){$('#main_content').html(data);});
    //alert(agr_id);
}