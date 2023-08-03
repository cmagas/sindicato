var chooser, btn;
 
function insertImage(data)
{
	inserta('<img src="'+data.url+'">');
}

function showVentanaImagen(obj)
{
	if(!chooser)
	{
		chooser = new ImageChooser	(obj);
	} 
	chooser.show(null,insertImage);
}
