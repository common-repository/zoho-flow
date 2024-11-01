function copyValue(elementId){
  var temp=document.createElement('input');
  var success_elementId = elementId.concat("","-copy-sucess");
  var texttoCopy=document.getElementById(elementId).innerHTML.trim();
  temp.type='input';
  temp.setAttribute('value',texttoCopy);
  document.body.appendChild(temp);
  temp.select();
  document.execCommand("copy"); //No I18N
  temp.remove();
  jQuery(document.getElementById(success_elementId)).hide();
  jQuery(document.getElementById(success_elementId)).css({"display":"inline-block"}); //No I18N
  setTimeout(function(){
    jQuery(document.getElementById(success_elementId)).hide();
  }, 1000);
}

jQuery(document).ready(function(){
  jQuery(".system-info-copy-icon").click(function(e){ //NO I18N
    var frm = jQuery(e.srcElement);
    var copy_element_id = e.target.id;
    if(copy_element_id && copy_element_id.endsWith("-copy-value")){
      copy_element_id = copy_element_id.replace('-copy-value','');
      copy_value_element_id = copy_element_id.concat("-","span"); //NO I18N
      copyValue(copy_value_element_id);
    }
  });
});
