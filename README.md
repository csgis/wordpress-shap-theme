# SHAP Theme

This theme can be used for SHAP development and testing synchronization with
EasyDB. For the later please visit the importer plugin.
https://github.com/dainst/wordpress-sha--importer

To receive SHAP Data for mapping this theme comes with a very basic
custom REST API. You can use the API with your theme as explained below.


## API installation
- copy folder `./mh` to your theme
- include following to the beginning of your functions.php

```
include dirname(__FILE__).'/mh/functions/helper.functions.php';
include dirname(__FILE__).'/mh/components/components.functions.php';
```

## Endpoints

**Request Places (Taxonomie)**

http://example.com/wp-json/shap/v1/shap__places

**Request media-attachements (importiertd by SHAP-Importer)**

http://example.com/wp-json/shap/v1/shap__medias/

**Paging options**

http://example.com/wp-json/shap/v1/shap__medias/?perPage=15&paged=1

- perPage (int)		maximum number of posts returned by request
- paged (int) 		defines returned page

**Places Filter (Filter media by places parent id)**

http://example.com/wp-json/shap/v1/shap__medias/?shap_places=24

- shap_places (int) 	parent `term_id`. Returns all media for place 
