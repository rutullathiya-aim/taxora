<line_number>: <original_line>. Please note that any changes targeting the original code should remove the 
    private function sanitize(): void\n236:     {\n237:         
        $this->company_name = $this->company_name === null ? null 
trim($this->company_name);\n239:         $this->email = strtolower(trim($this->email));\n240:         $this->phone = 
        $this->address = $this->address === null ? null : trim($this->address);\n242:        
trim($this->project_name);\n243:     }\n244: \n245:     
array\n246:     {\n247:         return Arr::only($validated, [\n248:    
            'company_name',\n250:             'email',\n251:             'phone',\n252:  
            'status',\n254:         ]);\n255:     }\n256: \n257:     public function 
View\n258:     {\n259:         return view('livewire.clients.client-form');\n260:     }\nThe above content 
2026-07-16T13:38:18Z\nCompleted At: 2026-07-16T13:38:18Z\nFile Path: 
243\nTotal Bytes: 
<line_number>: <original_line>. Please note that any changes targeting the original code should remove the 
<?php\n2: \n3: declare(strict_types=1);\n4: \n5: namespace 
\n7: use App\\Enums\\ClientAction;\n8: use App\\Enums\\ClientStatus;\n9: use 
use App\\Enums\\ServiceStatus;\n11: use App\\Models\\Client;\n12: use 
use App\\Services\\ClientManager;\n14: use Flux\\Flux;\n15: use 
use Illuminate\\Validation\\Rule;\n17: use Illuminate\\View\\View;\n18: use 
use Livewire\\Attributes\\On;\n20: use Livewire\\Component;\n21: \n22: class 
{\n24:     protected ClientManager $clientManager;\n25: \n26:     public bool 
\n28:     public ?string $editingClientId = null;\n29: \n30:     public string $client_name = 
\n32:     public string $company_name = '';\n33: \n34:     public string $email = '';\n35: \n36:     public 
\n38:     public string $address = '';\n39: \n40:     public string $status = 
\n42:     public ?string $service_id = null;\n43: \n44:     public string 
\n46:     public function boot(ClientManager $clientManager): void\n47:     {\n48:         