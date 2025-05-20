// Main JavaScript file for Research Survey Management Tool

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle dynamic question addition in survey creation
    const addQuestionBtn = document.getElementById('addQuestion');
    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            const questionContainer = document.createElement('div');
            questionContainer.className = 'question-container';
            questionContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <input type="text" class="form-control" name="questions[][text]" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Question Type</label>
                    <select class="form-select" name="questions[][type]" onchange="handleQuestionTypeChange(this)">
                        <option value="text">Text Input</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="single_choice">Single Choice</option>
                        <option value="rating">Rating</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Required</label>
                    <input type="checkbox" name="questions[][required]" value="1">
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">Remove Question</button>
            `;
            document.getElementById('questionsContainer').appendChild(questionContainer);
        });
    }

    // Handle question type change
    window.handleQuestionTypeChange = function(select) {
        const container = select.closest('.question-container');
        const optionsContainer = container.querySelector('.question-options') || document.createElement('div');
        optionsContainer.className = 'question-options mt-3';
        
        if (select.value === 'multiple_choice' || select.value === 'single_choice') {
            optionsContainer.innerHTML = `
                <div class="mb-2">
                    <label class="form-label">Options (one per line)</label>
                    <textarea class="form-control" name="questions[][options]" rows="3" required></textarea>
                </div>
            `;
            if (!container.querySelector('.question-options')) {
                container.appendChild(optionsContainer);
            }
        } else {
            optionsContainer.remove();
        }
    };

    // Remove question
    window.removeQuestion = function(button) {
        button.closest('.question-container').remove();
    };

    // Handle survey status change
    const statusSelect = document.getElementById('surveyStatus');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'active') {
                if (!confirm('Are you sure you want to activate this survey? This will make it available to participants.')) {
                    this.value = 'draft';
                }
            }
        });
    }

    // Handle CSV export
    const exportBtn = document.getElementById('exportResponses');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const surveyId = this.dataset.surveyId;
            window.location.href = `index.php?page=export&survey_id=${surveyId}`;
        });
    }
}); 