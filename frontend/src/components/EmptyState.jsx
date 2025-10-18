import { Link } from 'react-router-dom'

const EmptyState = ({ 
  icon = '📚', 
  title = 'Nema podataka', 
  description = 'Trenutno nema dostupnih podataka.',
  actionText = null,
  actionLink = null,
  actionOnClick = null
}) => {
  return (
    <div className="text-center py-12">
      <div className="text-6xl mb-4">{icon}</div>
      <h3 className="text-xl font-serif text-gothic-300 mb-2">{title}</h3>
      <p className="text-gothic-400 mb-6 max-w-md mx-auto">{description}</p>
      
      {actionText && (
        <div>
          {actionLink ? (
            <Link to={actionLink} className="btn-primary">
              {actionText}
            </Link>
          ) : actionOnClick ? (
            <button onClick={actionOnClick} className="btn-primary">
              {actionText}
            </button>
          ) : (
            <span className="text-accent-400">{actionText}</span>
          )}
        </div>
      )}
    </div>
  )
}

export default EmptyState
