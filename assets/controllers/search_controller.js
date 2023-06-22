import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["search_input","search_results"];
    async searching() {
    let keyword = this.search_inputTarget.value.trim();
    let res = [];
        try {
            await fetch("/search/" + keyword).then(response => response.json()).then(data => {
                data.forEach(d => {
                 console.log(d.name)
                 res.push(d)
                });
             })
             this.search_resultsTarget.textContent = res.map((p) => {
                 return p.name
             }) ;
        }
        catch(err){
            console.error("Une erreur s'est produite lors de la recherche :", err);
            this.search_resultsTarget.textContent = "";
        }
    }
} 
