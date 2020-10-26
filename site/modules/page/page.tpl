<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta charset="utf-8">
        <title>~~$page.title~</title>
        <meta name="keywords" content="~~$page.title~"/>
        <meta name="description" content="~~$page.title~"/>
        
        ~~include file="tpl/css.tpl"~
        ~~include file="tpl/js.tpl"~


    </head>
    <body>

				~~include file="tpl/open-menu.tpl"~

        <div class="page-wrap">
            <div class="page">
                
				~~include file="tpl/header.tpl"~

                <section class="main holder text-page ~~if $page.alias == 'about'~ about~~/if~  ~~if $page.alias == 'how-to-made-order'~ how-to-page~~/if~">
                    <div class="wrapper">
		    
					~~$page_content~
			
                    </div>
                </section>
		
				~~include file="tpl/footer.tpl"~		
		
            </div>
        </div>
	
        <a href="#" class="totop only-mobile">go to top</a>
        ~~include file="tpl/metrika.tpl"~
	    ~~*include file="tpl/ga.tpl"*~
    </body>
</html>