from database import SessionLocal
import crud
from models import User, MessageRole

db = SessionLocal()
try:
    user = db.query(User).first()
    if not user:
        print("No user found")
        exit(1)

    # 1. Create a dummy conversation
    conv = crud.create_conversation(db, user_id=user.id, title="Test Conversational Title")
    
    # 2. Add a message with a specific string
    msg = crud.create_message(
        db, 
        conversation_id=conv.id, 
        role=MessageRole.USER, 
        content="This is a superuniqueword inside the message content."
    )
    
    # 3. Search for the word in conversations
    search_query = "superuniqueword"
    results = crud.get_user_conversations(db, user_id=user.id, search_query=search_query)
    
    print(f"Number of conversations matching '{search_query}': {len(results)}")
    for c in results:
        print(f"Matched Conv ID: {c.id}, Title: {c.title}")
        
    # Cleanup
    crud.delete_conversation(db, conv.id, user.id)
    
except Exception as e:
    print(f"Error: {e}")
finally:
    db.close()
