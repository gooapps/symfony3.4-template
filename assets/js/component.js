import React from 'react';
import ReactDOM from 'react-dom';
import styles from './css/style.css';

class Component extends React.Component {
    constructor() {
        super();
    }

    componentDidMount() {
    }

    render() {
        return (
            <div>
                <h1 className="main--color"> GooApps</h1>
                <p>Component</p>
            </div>);
    }
}

ReactDOM.render(<Component />, document.getElementById('root'));