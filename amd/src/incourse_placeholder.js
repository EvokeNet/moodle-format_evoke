// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course index placeholder replacer.
 *
 * @module     core_courseformat/local/courseindex/placeholder
 * @class      core_courseformat/local/courseindex/placeholder
 * @copyright  2021 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {BaseComponent} from 'core/reactive';
import Templates from 'core/templates';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import Pending from 'core/pending';

/* eslint-disable */
export default class Component extends BaseComponent {

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {element|string} target the DOM main element or its ID
     * @param {object} selectors optional css selector overrides
     * @param {int} section section id
     * @return {Component}
     */
    static init(target, selectors, section) {
        self.section = section;

        return new Component({
            element: document.getElementById(target),
            reactive: getCurrentCourseEditor(),
            selectors,
        });
    }

    static section = 0;

    /**
     * Component creation hook.
     */
    create() {
        // Add a pending operation waiting for the initial content.
        this.pendingContent = new Pending(`core_courseformat/placeholder:loadcourseindex`);
    }

    /**
     * Initial state ready method.
     *
     * This stateReady to be async because it loads the real courseindex.
     *
     * @param {object} state the initial state
     */
    async stateReady(state) {
        await this.loadTemplateContent(state);
    }

    /**
     * Load the course index template.
     *
     * @param {Object} state the initial state
     */
    async loadTemplateContent(state) {
        // Collect section information from the state.
        const exporter = this.reactive.getExporter();
        const data = exporter.course(state);

        data.sections = data.sections.filter((section) => {
            return parseInt(section.id) == self.section;
        });

        console.log(data);

        try {
            // To render an HTML into our component we just use the regular Templates module.
            const {html, js} = await Templates.renderForPromise(
                'core_courseformat/local/courseindex/courseindex',
                data,
            );
            Templates.replaceNode(this.element, html, js);
            this.pendingContent.resolve();

            // Save the rendered template into the session cache.
            this.reactive.setStorageValue(`courseIndex`, {html, js});
        } catch (error) {
            this.pendingContent.resolve(error);
            throw error;
        }
    }
}
