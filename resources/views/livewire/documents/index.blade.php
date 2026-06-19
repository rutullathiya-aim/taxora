<div>
    <x-documents.stats :stats="$this->stats" />
    <x-documents.grid :documents="$this->documents" />
    <x-documents.viewer-modal model="showPreviewModal" :document-id="$previewDocumentId" />
    <x-documents.rename-modal />
</div>