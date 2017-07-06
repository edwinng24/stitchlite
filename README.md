# stitchlite

Stitchlite is a Laravel-based php application that communicates with Shopify and Vend’s respective API’s to download product information. It offers a set of json-based API that provides user insight on product info across different sales channels (shopify and vend).

The application follows most of the default file layout of Laravel with the 
exception of the model files which is placed under /app/Models instead of 
the default /app folder. 

# Running the application with Valet

* Clone the repository
* composer install
* php artisan key:generate
* Install .env
* You can then access the api via http://stitchlite.dev/api/products


