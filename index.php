<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grafo Geografico</title>
    <script src="d3.v6.min.js"></script> <!----  https://d3js.org/ ---->
    <style>
        svg {
            background-color: #F5F5F5;
        }

        .link {
            stroke: #999;
            stroke-opacity: 0.6;
        }

        .node circle {
            fill: #fff;
            stroke: #666;
            stroke-width: 2px;
        }

        .node text {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <svg width="800" height="600"></svg>

    <script>
        // Dati di esempio
        var dati = [
            ['Società A', 'Via Ferrari,Bologna', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna'],
            ['Società B', 'Via Colonna 1, Roma', 'Amministratore 2', 'Via Bologna 1, Roma'],
            ['Società C', 'Via Bologna 1, Firenze', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna'],
            // Aggiungi altri dati se necessario
        ];

        // Creazione del grafo
        var grafo = { nodes: [], links: [] };

        // Itera sui dati
        dati.forEach(function (riga) {
            var societa = riga[0];
            var indirizzoSocieta = riga[1];
            var amministratore = riga[2];
            var indirizzoAmministratore = riga[3];

            // Aggiungi nodi al grafo
            if (!grafo.nodes.find(function (n) { return n.id === societa; })) {
                grafo.nodes.push({ id: societa, address: indirizzoSocieta });
            }
            if (!grafo.nodes.find(function (n) { return n.id === amministratore; })) {
                grafo.nodes.push({ id: amministratore, address: indirizzoAmministratore });
            }

            // Aggiungi collegamenti al grafo
            grafo.links.push({ source: societa, target: amministratore });
        });

        // Creazione della visualizzazione del grafo
        var svg = d3.select("svg");
        var width = +svg.attr("width");
        var height = +svg.attr("height");

        var simulation = d3.forceSimulation()
            .force("link", d3.forceLink().id(function (d) { return d.id; }))
            .force("charge", d3.forceManyBody())
            .force("center", d3.forceCenter(width / 2, height / 2));

        var link = svg.append("g")
            .attr("class", "links")
            .selectAll("line")
            .data(grafo.links)
            .enter().append("line")
            .attr("class", "link");

        var node = svg.append("g")
            .attr("class", "nodes")
            .selectAll("g")
            .data(grafo.nodes)
            .enter().append("g");

        var circles = node.append("circle")
            .attr("r", 10)
            .attr("fill", function (d) {
                return (d.id.startsWith("Società")) ? "blue" : "green";
            });

        var labels = node.append("text")
            .attr("x", 15)
            .attr("y", 5)
            .text(function (d) { return d.id; });

        simulation
            .nodes(grafo.nodes)
            .on("tick", ticked);

        simulation.force("link")
            .links(grafo.links);

        function ticked() {
            link
                .attr("x1", function (d) { return d.source.x; })
                .attr("y1", function (d) { return d.source.y; })
                .attr("x2", function (d) { return d.target.x; })
                .attr("y2", function (d) { return d.target.y; });

            node
                .attr("transform", function (d) {
                    return "translate(" + d.x + "," + d.y + ")";
                });
        }
    </script>
</body>
</html>
