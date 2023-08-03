function cLienzo(obj)
{
	this.contenedor=obj.contenedor;
    this.ancho=obj.ancho;
    this.alto=obj.alto;
    this.escena = new Kinetic.Stage(	
                                        {
                                            container: this.contenedor,
                                            width: this.ancho,
                                            height: this.alto
                                        }
                                     );
	                                     
}

cLienzo.prototype.agregarObjeto=function(obj)
                                {
                                    
                                    this.escena.add(obj);
                                }
								
								
function dibujarLinea2P(obj,capa)
{
	
	var linea=new Kinetic.Line	(
									{
										points: [obj.x1,obj.y1,obj.x2,obj.y2],
									  	stroke: "#000000",
									  	strokeWidth: 1,
									 	lineCap: "round",
									  	lineJoin: "round"	
									}
								);
	if(capa!=null)
	{
		capa.clear();
	}
	
	capa.add(linea);
}