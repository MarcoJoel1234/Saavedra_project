let wOrderArray = window.wOInProgress;

class Dashboard {
    constructor(wOrderArray) {
        this.wOrderArray = wOrderArray;
    }
    //Función para general carrusel
    generateSection($workOrder, $class, $processes) {
        let divHeader = document.createElement("div");
        divHeader.className = "header";
        let workOrderDiv = document.createElement("div");
        workOrderDiv.className = "work-order";
        let h2 = document.createElement("h2");
        h2.className = "work-order-title text-header";
        h2.innerHTML = "Orden de trabajo: " + $workOrder;
        let moldingLabel = document.createElement("label");
        moldingLabel.className = "molding-label text-header";
        label.innerHTML = "Moldura: " + $workOrder["moldura"];
        let classLabel = document.createElement("label");
        classLabel.className = "class-label text-header";
        classLabel.innerHTML = "Clase: " + $class;

        //Insertar los elementos en el div
        workOrderDiv.appendChild(h2);
        workOrderDiv.appendChild(moldingLabel);
        workOrderDiv.appendChild(classLabel);
        divHeader.appendChild(workOrderDiv);
        section.appendChild(divHeader);
        return section;
    }
    //prettier-ignore
    createSections() {
        let body = document.querySelector("body");
        Object.values(this.wOrderArray).forEach((workOrder, indexWo) => {
            let wOrderName = Object.keys(this.wOrderArray)[indexWo];
            Object.values(workOrder["classes"]).forEach((classArray, indexClass) => {
                let section = document.createElement("section");
                section.className = "section";
                let className = Object.keys(workOrder["classes"])[indexClass];
                let headerSection = this.generateHeaderofWorkOrder( wOrderName, workOrder["molding"], className, classArray);
                let processesSection = document.createElement("div");
                processesSection.className = "processes-section";
            
                Object.values(classArray["processes"]).forEach((processesArray, indexProcess) => {
                    let processName = Object.keys(classArray["processes"])[indexProcess];
                    processesSection.appendChild(this.generateProcessSection(processesArray, processName, classArray["pieces"]));
                });
                section.appendChild(headerSection);
                section.appendChild(processesSection);
                body.appendChild(section);
            });
        });
    }
    generateHeaderofWorkOrder(wOrderName, moldingName, className, classArray) {
        let valueText = [
            [
                `OT: ${wOrderName}`,
                `Moldura: ${moldingName}`,
                `Clase: ${className}`,
            ],
            [
                `Pedido: 0/${classArray["pieces"]}`,
                `Fecha de inicio: ${classArray["startDate"]}`,
                `Fecha de término: ${classArray["endDate"]}`,
            ],
        ];
        let classText = [
            ["workOrder-text", "molding-text", "class-text"],
            ["pieces-text", "start-date-text", "end-date-text"],
        ];

        let header_section = document.createElement("divHeader");
        header_section.className = "header-section";

        for (let i = 0; i < valueText.length; i++) {
            let div = document.createElement("div");
            div.className = "title-div";
            for (let j = 0; j < valueText[i].length; j++) {
                let h3 = document.createElement("h3");
                h3.className = classText[i][j];
                h3.classList.add("text-header-class");
                h3.innerHTML = valueText[i][j];

                div.appendChild(h3);
            }
            header_section.appendChild(div);
        }
        return header_section;
    }

    generateProcessSection(processesArray, processName, order) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerHTML = processName;
        processSection.appendChild(processTitle);
        //Crear barra de progreso
        let progressBar = document.createElement("div");
        progressBar.className = "progress-bar";

        let pieces = [
            processesArray["pieces"]["good"],
            processesArray["pieces"]["bad"],
        ];
        for (let i = 0; i < pieces.length; i++) {
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress" : "bad-progress";
            progress.classList.add("progress");
            let percentage = (pieces[i] * 100) / order;
            progress.style.width = `${percentage}%`;

            percentage = percentage != 0 ? percentage.toFixed(1) : 0;
            let div = document.createElement("div");
            div.className = "progress-percentage";
            div.innerHTML = `${percentage}%`;

            progressBar.appendChild(progress);
            progressBar.appendChild(div);
            processSection.appendChild(progressBar);
        }

        //Agregar evento al div de progreso
        processSection.addEventListener("click", () => {
            let div = document.createElement("div");
            div.className = "process-details";            
        });
        return processSection;
    }
}

if (Object.keys(wOrderArray).length > 0) {
    let dashboard = new Dashboard(wOrderArray);
    dashboard.createSections();
}
