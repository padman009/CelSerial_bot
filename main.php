<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
    <script>
        async function getData(url, method, headers={}) {
            let request = {
                method: method,
            };

            if(headers !== {}){request['headers'] = headers};

            let res = await fetch(url, request);
            return await res.text();
        }

        async function uploadData(url, method, data, headers={}) {
            let request = {
                method: method,
                body: JSON.stringify(data),
                mode: 'no-cors'
            };

            if(headers !== {}){request['headers'] = headers};

            let res = await fetch(url, request);
            return await res.json();
        }

        getData("https://rezka.ag/", "GET", {"User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"}).then(fullHtml => {

            let dom = new DOMParser();
            let doc = dom.parseFromString(fullHtml, "text/html");
            let mainDiv = doc.querySelector("div[class=\"b-seriesupdate__block\"]");
        });

        function parseEpisodesInDiv(htmlDiv) {

        }

        uploadData("index.php", "POST", {"send":"1", "text":"suc"});
    </script>
</body>
</html>